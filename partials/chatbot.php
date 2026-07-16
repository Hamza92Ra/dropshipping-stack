<?php
/**
 * Dropshipping Support Chatbot Partial
 * Place this file inside your /partials folder
 */
?>
<div class="chat-launcher" id="chatLauncher">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
    </svg>
</div>

<div class="chat-window" id="chatWindow">
    <div class="chat-header">
        <span>Store Support Assistant</span>
        <span class="chat-close" id="chatClose">&times;</span>
    </div>
    <div class="chat-body" id="chatBody">
        <div class="msg bot">Hello! Welcome to our store. How can I assist you with your dropshipping questions today?</div>
    </div>
    <div class="chat-options" id="chatOptions"></div>
</div>

<style>
    .chat-launcher {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 60px;
        height: 60px;
        background-color: #007bff;
        color: white;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        cursor: pointer;
        z-index: 1000;
        transition: transform 0.2s ease;
    }
    .chat-launcher:hover { transform: scale(1.05); }
    .chat-window {
        position: fixed;
        bottom: 90px;
        right: 20px;
        width: 350px;
        max-width: 90%;
        height: 450px;
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.2);
        display: none;
        flex-direction: column;
        overflow: hidden;
        z-index: 1000;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .chat-header {
        background-color: #007bff;
        color: white;
        padding: 15px;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .chat-close { cursor: pointer; font-size: 24px; line-height: 1; }
    .chat-body {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        background-color: #f8f9fa;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .msg {
        max-width: 80%;
        padding: 10px 14px;
        border-radius: 15px;
        font-size: 14px;
        line-height: 1.4;
        word-wrap: break-word;
    }
    .msg.bot {
        background-color: #e9ecef;
        color: #333;
        align-self: flex-start;
        border-bottom-left-radius: 2px;
    }
    .msg.user {
        background-color: #007bff;
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 2px;
    }
    .chat-options {
        padding: 10px 15px;
        background-color: #fff;
        border-top: 1px solid #eee;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        max-height: 120px;
        overflow-y: auto;
    }
    .opt-btn {
        background-color: #f1f3f5;
        border: 1px solid #dee2e6;
        color: #495057;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .opt-btn:hover {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }
</style>

<script>
    const botData = {
        start: {
            options: [
                { text: "What is your shipping time?", next: "shipping" },
                { text: "How do I track my order?", next: "tracking" },
                { text: "What is your return policy?", next: "returns" },
                { text: "Do you ship internationally?", next: "international" }
            ]
        },
        shipping: {
            text: "Our standard processing time is 1-3 business days. Shipping usually takes between 7 to 15 business days depending on your location.",
            options: [{ text: "Back to Main Menu", next: "start" }]
        },
        tracking: {
            text: "Once your package ships, a tracking number will automatically be sent to your email. You can check it live via tracking links on our site.",
            options: [{ text: "Back to Main Menu", next: "start" }]
        },
        returns: {
            text: "We offer a 30-day money-back guarantee! If your item arrives damaged or incorrect, contact us for a free replacement or return.",
            options: [{ text: "Back to Main Menu", next: "start" }]
        },
        international: {
            text: "Yes! We offer free worldwide shipping to most major countries.",
            options: [{ text: "Back to Main Menu", next: "start" }]
        }
    };

    const launcher = document.getElementById('chatLauncher');
    const windowEl = document.getElementById('chatWindow');
    const closeBtn = document.getElementById('chatClose');
    const chatBody = document.getElementById('chatBody');
    const chatOptions = document.getElementById('chatOptions');

    launcher.addEventListener('click', () => {
        windowEl.style.display = 'flex';
        launcher.style.display = 'none';
    });

    closeBtn.addEventListener('click', () => {
        windowEl.style.display = 'none';
        launcher.style.display = 'flex';
    });

    function loadStep(stepKey) {
        const data = botData[stepKey];
        if (data.text) {
            const botMsg = document.createElement('div');
            botMsg.className = 'msg bot';
            botMsg.innerText = data.text;
            chatBody.appendChild(botMsg);
        }
        chatOptions.innerHTML = '';
        if (data.options) {
            data.options.forEach(opt => {
                const btn = document.createElement('button');
                btn.className = 'opt-btn';
                btn.innerText = opt.text;
                btn.addEventListener('click', () => {
                    const userMsg = document.createElement('div');
                    userMsg.className = 'msg user';
                    userMsg.innerText = opt.text;
                    chatBody.appendChild(userMsg);
                    chatBody.scrollTop = chatBody.scrollHeight;
                    setTimeout(() => {
                        loadStep(opt.next);
                        chatBody.scrollTop = chatBody.scrollHeight;
                    }, 300);
                });
                chatOptions.appendChild(btn);
            });
        }
    }
    loadStep('start');
</script>