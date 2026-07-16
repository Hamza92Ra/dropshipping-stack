<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * /api/llm-config.php
 *
 * Holds the Groq API key used by chat-api.php's general fallback.
 * Groq's free tier needs NO credit card and NO payment — it's rate-limited
 * (not billed), which is enough for a small site's chatbot traffic.
 *
 * Get a free key:
 *   1. Go to https://console.groq.com
 *   2. Sign up with email or Google (no card required)
 *   3. Go to API Keys -> Create API Key
 *   4. Paste it below
 *
 * Keep this file OUTSIDE any web-accessible path if possible, or make sure
 * your server config denies direct HTTP access to it (same as db-config.php).
 */
$groqApiKey = GROQ_API_KEY;
