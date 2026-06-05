ConfHub: AI-Powered Academic Conference Search & Community Hub

ConfHub is a comprehensive, full-stack web ecosystem designed to bridge the growing gap between academic event discovery and peer-to-peer developer engagement. In today's fragmented research landscape, academics and tech professionals often struggle to find relevant conferences scattered across disparate, static websites. ConfHub solves this by combining an intelligent, LLM-powered search microservice with a dynamic, Reddit-style social community platform, creating a centralized nexus for discovery, discussion, and networking.

$$PLACEHOLDER: Add your main website screenshot here$$


Replace this line with: ![ConfHub Dashboard](images/screenshot-main.png)

Core Features

1. Intelligent Conference Search Engine

At the heart of ConfHub is a robust, federated search infrastructure that removes the friction of hunting down event details.

Federated Scraping & Aggregation: Rather than relying on a single, limited database, ConfHub aggregates real-time research metadata and upcoming event schedules simultaneously. It leverages the OpenAlex Graph API for deep academic networking, Semantic Scholar indexes for paper and author relevance, and public DuckDuckGo text parsers to catch brand-new, unindexed web updates. This ensures users receive the most comprehensive event data available.

AI Chatbot Assistant: Searching with rigid keywords is a thing of the past. ConfHub integrates a dedicated Python Flask microservice utilizing the Hugging Face Meta-Llama-3-8B-Instruct large language model. This allows users to ask natural, contextual questions (e.g., "Find me upcoming cybersecurity workshops in Europe this fall" or "Summarize the submission guidelines for this AI symposium"). The LLM processes the aggregated data and returns concise, conversational, and highly accurate summaries.

Smart Caching & Rate Limit Protection: To guarantee lightning-fast response times and protect against third-party API rate-limiting, the engine employs programmatic response caching. By utilizing JSON serialization mapped to unique MD5 request hashes, the system serves repeated or similar queries instantly from memory, preventing bottlenecking during heavy search loads and ensuring a seamless user experience.

2. Reddit-Style Community Hub

Discovery is only half the journey. ConfHub features an interactive social layer built to foster meaningful discussions around the events users find.

Interactive Post Composer: Users can seamlessly publish updates, ask technical questions, and share multimedia posts to a global community feed. Whether sharing a Call for Papers (CFP), coordinating travel plans, or discussing a keynote speech, the composer supports rich engagement.

Dynamic Engagement Mechanics: The platform utilizes asynchronous (AJAX-style) components for a fluid, app-like feel. This includes single-click post likes to surface the most valuable content meritocratically, threaded comment sections, and nested peer-to-peer discussions that allow for deep technical dives without cluttering the main timeline.

Secure User Profiles: Identity and reputation are key in academic and developer circles. ConfHub provides secure, customized user profiles where individuals can display their event hosting history, track their engagement analytics, and showcase their specific topics of interest (e.g., Bioinformatics, Machine Learning, UI/UX Design).

3. Premium Unified Architecture

A powerful backend requires an equally sophisticated frontend. ConfHub's user interface is built with maintainability and modern aesthetics in mind.

Centralized Layouts: Adhering to the DRY (Don't Repeat Yourself) principle, the application uses a globally reusable navigation and layout wrapper (navbar.php). This component-based approach ensures absolute visual consistency across all endpoints, meaning a change to the branding or navigation structure only needs to be made in one place to update the entire application.

Responsive, Utility-First Design: Utilizing a clean, modern CSS architecture powered by Tailwind CSS, the platform is fully optimized for all devices. Whether viewed on a 4K desktop monitor, a tablet, or a mobile phone on the go, the fluid flexbox grids and custom SVG iconography adapt flawlessly to provide an immersive experience.

4. Self-Healing Database Infrastructure

ConfHub eliminates the traditional headaches of local environment setups and database configuration for new developers.

Automated Zero-Config Migrations: The MySQL backend is designed to be entirely self-healing. Upon initialization, the core connection scripts automatically verify the existence and integrity of required tables (users, posts, comments, events, likes). If a table or specific column schema is missing, the application dynamically constructs it on the fly. This "plug-and-play" architecture means you never have to manually import .sql dumps to get the app running.

UI Showcase Gallery

Community Feed & Discussions

$$PLACEHOLDER: Add your post.php screenshot here$$


Replace this line with: ![Community Feed](images/screenshot-feed.png)

User Profile & Analytics Panel

$$PLACEHOLDER: Add your profile.php screenshot here$$


Replace this line with: ![User Profile](images/screenshot-profile.png)

AI Search & Chatbot Interface

$$PLACEHOLDER: Add your chatbot search screenshot here$$


Replace this line with: ![AI Search](images/screenshot-search.png)

Technology Stack

The platform leverages a hybrid stack, taking advantage of PHP's rapid server-side templating alongside Python's unparalleled data and AI ecosystem.

Component

Technology

Description / Purpose

Front-End UI

HTML5, Tailwind CSS, Vanilla JS

Delivers fluid layouts, responsive flexboxes, custom SVG iconography, and asynchronous DOM updates without heavy frameworks.

Backend Controller

PHP (>= 8.0)

Handles global routing, secure session state management, form processing, and direct relational database context.

AI Microservice

Python 3 (Flask, Requests)

Operates as a standalone API to execute multi-source web scrapers and interface with the Hugging Face LLaMa-3 inference layer.

Database Layer

MySQL / MariaDB

Provides robust relational indexing, cascading foreign key constraints for data integrity, and self-healing schema creation.

Local Setup & Installation

Follow these steps to deploy the ConfHub environment on your local machine for development and testing.

Prerequisites

Web Server Stack: A local server environment such as XAMPP, MAMP, or a custom LAMP/WAMP stack.

Python Environment: Python 3.8 or higher installed and added to your system PATH.

API Access: A registered Hugging Face account with a generated API Access Token (to communicate with the LLaMa-3 model).

Step 1: Web Server Initialization

Clone this repository or extract the downloaded files directly into your local web server's public execution directory (e.g., C:/xampp/htdocs/confhub/ or /var/www/html/confhub/).

Open your database administration tool (such as phpMyAdmin or a CLI client) and create a brand-new, empty database named exactly login_db.

Start your Apache and MySQL services via your server control panel.

Navigate to the project URL (e.g., http://localhost/confhub/) in your web browser. The application's self-healing PHP scripts will automatically detect the empty database and build the necessary table structures instantly.

Step 2: AI Microservice Configuration

Open your command prompt or terminal and navigate into the root project directory.

Install the required Python dependencies to power the API and AI routing:

pip install flask flask-cors requests huggingface_hub duckduckgo_search python-dotenv


To secure your credentials, create a .env file in the root directory. Add your Hugging Face API key as follows (this file is git-ignored for your safety):

HF_TOKEN=your_huggingface_token_here


Boot up the local Flask AI server:

python chatbot.py


The Flask microservice endpoint will initialize and actively listen for front-end requests on http://localhost:5000.

Project Status & Future Roadmap

This repository currently serves as a highly functional architectural prototype. It has been expressly designed to demonstrate rapid feature loops, unified user interface architecture, and the viability of cross-language stack connectivity (bridging native PHP with Python-based AI microservices).

Planned Future Enhancements:

Implementation of OAuth 2.0 for quick social logins (Google/GitHub).

Migration of community feed interactions to WebSockets for real-time, live-updating comment streams.

Enhanced user dashboard analytics detailing conference attendance and networking metrics.
