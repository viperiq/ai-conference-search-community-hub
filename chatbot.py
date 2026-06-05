import os
import time
import hashlib
import json
import traceback
from flask import Flask, request, jsonify, render_template
from flask_cors import CORS
import requests
from huggingface_hub import InferenceClient

# =============================
# App setup
# =============================
app = Flask(__name__)
CORS(app)

# =============================
# Hugging Face setup
# =============================
# Use environment variables to avoid hardcoding sensitive API keys
HF_TOKEN = os.getenv("HF_TOKEN")
REPO_ID = "meta-llama/Meta-Llama-3-8B-Instruct"

if not HF_TOKEN:
    raise RuntimeError("HF_TOKEN environment variable is missing. Please set it before running the server.")

client = InferenceClient(
    model=REPO_ID,
    token=HF_TOKEN
)

# =============================
# Cache config
# =============================
CACHE_DIR = "cache"
CACHE_TTL = 3600  # 1 hour
if not os.path.exists(CACHE_DIR):
    os.makedirs(CACHE_DIR)

def cache_get(key):
    path = os.path.join(CACHE_DIR, key + ".json")
    if os.path.exists(path) and (time.time() - os.path.getmtime(path)) < CACHE_TTL:
        with open(path, "r", encoding="utf-8") as f:
            return json.load(f)
    return None

def cache_set(key, data):
    path = os.path.join(CACHE_DIR, key + ".json")
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)

# =============================
# Conference search functions
# =============================
def search_semanticscholar(query):
    url = "https://api.semanticscholar.org/graph/v1/paper/search"
    params = {"query": f"conference {query}", "limit": 20, "fields": "title,abstract,venue,year,url"}
    try:
        resp = requests.get(url, params=params, timeout=15, headers={
            "Accept": "application/json", "User-Agent": "Academic-Conference-Search/1.0"
        })
        resp.raise_for_status()
        data = resp.json().get("data", [])
        results = []
        for p in data:
            if not p.get("url") or not p.get("year") or int(p["year"]) < time.localtime().tm_year:
                continue
            snippet = f"{p.get('venue', '')} {p.get('year', '')} {p.get('abstract', '')}"
            results.append({"title": p.get("title", ""), "link": p["url"], "snippet": snippet})
        return results
    except:
        return []

def search_openalex(query):
    url = "https://api.openalex.org/works"
    params = {"search": f"conference {query}", "per-page": 20}
    try:
        resp = requests.get(url, params=params, timeout=15)
        resp.raise_for_status()
        data = resp.json().get("results", [])
        results = []
        for w in data:
            if not w.get("id") or not w.get("publication_year") or int(w["publication_year"]) < time.localtime().tm_year:
                continue
            snippet = f"{w.get('host_venue', {}).get('display_name', '')} {w.get('publication_year', '')}"
            results.append({"title": w.get("title", ""), "link": w["id"], "snippet": snippet})
        return results
    except:
        return []

def search_duckduckgo(query):
    from duckduckgo_search import DDGS
    try:
        results = DDGS().text(f"conference {query}", max_results=3)
        cleaned = []
        for r in results:
            snippet = r.get("body", "")
            if snippet:
                cleaned.append({"title": r.get("title", ""), "link": r.get("href", ""), "snippet": snippet})
        return cleaned
    except:
        return []

def search_conferences(query):
    key = hashlib.md5(query.encode()).hexdigest()
    cached = cache_get(key)
    if cached:
        return cached

    results = []
    results.extend(search_semanticscholar(query))
    results.extend(search_openalex(query))
    results.extend(search_duckduckgo(query))

    # Deduplicate by link
    seen = set()
    unique = []
    for r in results:
        if r["link"] not in seen:
            seen.add(r["link"])
            unique.append(r)

    cache_set(key, unique[:10])
    return unique[:10]

# =============================
# Smart search detection
# =============================
CONFERENCE_KEYWORDS = [
    "conference", "symposium", "workshop", "call for papers", "cfp", "event"
]

def should_search(query):
    """Return True if the query looks like it needs a conference search."""
    q = query.lower()
    return any(kw in q for kw in CONFERENCE_KEYWORDS)

# =============================
# Routes
# =============================
@app.route("/", methods=["GET"])
def index():
    return render_template("index.html")  # Use Messenger-style UI

@app.route("/chat", methods=["POST"])
def chat():
    data = request.json
    user_message = data.get("message", "").strip()

    # Decide if we should search
    search_results = []
    if should_search(user_message):
        search_results = search_conferences(user_message)
        system_prompt = (
            "You are a helpful assistant. Summarize the following academic conference info "
            "into a concise answer. Only include current/upcoming events and ignore expired or irrelevant ones.\n\n"
            f"{json.dumps(search_results, indent=2)}"
        )
    else:
        system_prompt = "You are a helpful assistant specialized in academic and technical topics."

    messages = [
        {"role": "system", "content": system_prompt},
        {"role": "user", "content": user_message}
    ]

    try:
        response = client.chat.completions.create(
            model=REPO_ID,
            messages=messages,
            max_tokens=500,
            temperature=0.5
        )

        ai_text = ""
        if hasattr(response, "choices") and len(response.choices) > 0:
            choice = response.choices[0]
            ai_text = getattr(choice.message, "content", str(choice))

        return jsonify({
            "response": ai_text,
            "sources": search_results
        })

    except Exception as e:
        traceback.print_exc()
        return jsonify({
            "response": "The model encountered an error.",
            "error": str(e),
            "sources": search_results
        }), 500

# =============================
# Run server
# =============================
if __name__ == "__main__":
    print("Server running on http://localhost:5000")
    app.run(debug=False, host="0.0.0.0", port=5000)