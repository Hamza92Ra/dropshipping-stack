/**
 * DropshippingStack Chat Widget
 * Drop this single script tag near the end of your <body> (e.g. in partials/footer.php):
 *   <script src="/assets/chat-widget.js" data-api="/api/chat.php"></script>
 *
 * It injects its own CSS + HTML, so nothing else needs to change on the page.
 */
(function () {
  const SCRIPT = document.currentScript;
  const API_URL = (SCRIPT && SCRIPT.getAttribute('data-api')) || '/api/chat.php';

  // ---------- styles ----------
  const css = `
  :root{
    --ds-purple:#6c5ce7;
    --ds-purple-dark:#5848c2;
    --ds-orange:#f5a623;
    --ds-dark:#11121a;
    --ds-dark-2:#1a1c28;
    --ds-bg:#f3f4f6;
    --ds-green:#16a34a;
    --ds-text:#1f2230;
  }
  #ds-chat-bubble{
    position:fixed; bottom:22px; right:22px; width:58px; height:58px; border-radius:50%;
    background:linear-gradient(135deg,var(--ds-purple),var(--ds-purple-dark));
    box-shadow:0 8px 24px rgba(108,92,231,.45);
    display:flex; align-items:center; justify-content:center; cursor:pointer;
    z-index:99998; border:none; transition:transform .18s ease, box-shadow .18s ease;
  }
  #ds-chat-bubble:hover{ transform:translateY(-2px) scale(1.04); box-shadow:0 12px 28px rgba(108,92,231,.55); }
  #ds-chat-bubble svg{ width:26px; height:26px; }
  #ds-chat-panel{
    position:fixed; bottom:92px; right:22px; width:370px; max-width:92vw; height:540px; max-height:75vh;
    background:#fff; border-radius:18px; overflow:hidden; display:none; flex-direction:column;
    box-shadow:0 20px 60px rgba(17,18,26,.35); z-index:99999; font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
  }
  #ds-chat-panel.open{ display:flex; animation:dsSlideUp .22s ease; }
  @keyframes dsSlideUp{ from{ opacity:0; transform:translateY(14px);} to{opacity:1; transform:translateY(0);} }
  #ds-chat-header{
    background:linear-gradient(120deg,var(--ds-dark),var(--ds-dark-2));
    padding:16px 18px; color:#fff; display:flex; align-items:center; gap:10px; flex-shrink:0;
  }
  #ds-chat-header .ds-bolt{ color:var(--ds-orange); font-size:18px; }
  #ds-chat-header-text{ flex:1; }
  #ds-chat-header-text strong{ display:block; font-size:14.5px; }
  #ds-chat-header-text span{ display:block; font-size:11.5px; color:#a9acc0; margin-top:1px; }
  #ds-chat-close{ background:none; border:none; color:#a9acc0; cursor:pointer; font-size:18px; line-height:1; padding:4px; }
  #ds-chat-close:hover{ color:#fff; }
  #ds-chat-messages{ flex:1; overflow-y:auto; padding:16px; background:var(--ds-bg); display:flex; flex-direction:column; gap:10px; }
  .ds-msg{ max-width:84%; padding:9px 13px; border-radius:14px; font-size:13.5px; line-height:1.45; }
  .ds-msg.bot{ background:#fff; color:var(--ds-text); align-self:flex-start; border:1px solid #e7e8ee; border-bottom-left-radius:4px; }
  .ds-msg.user{ background:var(--ds-purple); color:#fff; align-self:flex-end; border-bottom-right-radius:4px; }
  .ds-chips{ display:flex; flex-wrap:wrap; gap:6px; margin-top:2px; }
  .ds-chip{
    background:#fff; border:1px solid #ddd0ff; color:var(--ds-purple-dark); font-size:12px;
    padding:6px 10px; border-radius:999px; cursor:pointer; transition:background .15s;
  }
  .ds-chip:hover{ background:#f1edff; }
  #ds-chat-input-row{ display:flex; gap:8px; padding:12px; border-top:1px solid #ececf2; background:#fff; flex-shrink:0; }
  #ds-chat-input{
    flex:1; border:1px solid #e0e1e8; border-radius:999px; padding:10px 14px; font-size:13.5px;
    outline:none; font-family:inherit;
  }
  #ds-chat-input:focus{ border-color:var(--ds-purple); }
  #ds-chat-send{
    background:var(--ds-purple); border:none; color:#fff; width:38px; height:38px; border-radius:50%;
    display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; transition:background .15s;
  }
  #ds-chat-send:hover{ background:var(--ds-purple-dark); }
  #ds-chat-send:disabled{ opacity:.5; cursor:default; }
  .ds-typing{ display:flex; gap:4px; padding:9px 13px; }
  .ds-typing span{ width:6px; height:6px; border-radius:50%; background:#bfc1cc; animation:dsBlink 1.2s infinite; }
  .ds-typing span:nth-child(2){ animation-delay:.2s; }
  .ds-typing span:nth-child(3){ animation-delay:.4s; }
  @keyframes dsBlink{ 0%,80%,100%{ opacity:.3; } 40%{ opacity:1; } }
  @media (max-width:480px){
    #ds-chat-panel{ right:10px; left:10px; width:auto; bottom:84px; }
    #ds-chat-bubble{ right:16px; bottom:16px; }
  }
  `;
  const style = document.createElement('style');
  style.textContent = css;
  document.head.appendChild(style);

  // ---------- markup ----------
  const bubble = document.createElement('button');
  bubble.id = 'ds-chat-bubble';
  bubble.setAttribute('aria-label', 'Open chat assistant');
  bubble.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>`;

  const panel = document.createElement('div');
  panel.id = 'ds-chat-panel';
  panel.innerHTML = `
    <div id="ds-chat-header">
      <span class="ds-bolt">⚡</span>
      <div id="ds-chat-header-text">
        <strong>DropshippingStack Assistant</strong>
        <span>Tools, pricing &amp; comparisons</span>
      </div>
      <button id="ds-chat-close" aria-label="Close chat">✕</button>
    </div>
    <div id="ds-chat-messages"></div>
    <div id="ds-chat-input-row">
      <input id="ds-chat-input" type="text" placeholder="Ask about a tool, e.g. 'Shopify vs Zendrop'" />
      <button id="ds-chat-send" aria-label="Send">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
      </button>
    </div>
  `;

  document.body.appendChild(bubble);
  document.body.appendChild(panel);

  const messagesEl = panel.querySelector('#ds-chat-messages');
  const inputEl = panel.querySelector('#ds-chat-input');
  const sendBtn = panel.querySelector('#ds-chat-send');
  const closeBtn = panel.querySelector('#ds-chat-close');

  const STARTER_CHIPS = [
    'Compare Shopify vs Zendrop',
    'Best tools under $50/mo',
    'What is a "stack"?'
  ];

  let history = []; // {role, content}
  let opened = false;

  function addMessage(role, text) {
    const div = document.createElement('div');
    div.className = 'ds-msg ' + (role === 'user' ? 'user' : 'bot');
    div.textContent = text;
    messagesEl.appendChild(div);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function addChips() {
    const wrap = document.createElement('div');
    wrap.className = 'ds-chips';
    STARTER_CHIPS.forEach((label) => {
      const chip = document.createElement('button');
      chip.className = 'ds-chip';
      chip.textContent = label;
      chip.onclick = () => sendMessage(label);
      wrap.appendChild(chip);
    });
    messagesEl.appendChild(wrap);
  }

  function showTyping() {
    const div = document.createElement('div');
    div.className = 'ds-msg bot ds-typing-wrap';
    div.innerHTML = `<div class="ds-typing"><span></span><span></span><span></span></div>`;
    div.style.padding = '0';
    messagesEl.appendChild(div);
    messagesEl.scrollTop = messagesEl.scrollHeight;
    return div;
  }

  async function sendMessage(text) {
    text = (text || inputEl.value).trim();
    if (!text) return;
    inputEl.value = '';
    sendBtn.disabled = true;
    addMessage('user', text);
    history.push({ role: 'user', content: text });

    const typingEl = showTyping();
    try {
      const res = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text, history: history.slice(-10) })
      });
      const data = await res.json();
      typingEl.remove();
      const reply = data.reply || "Sorry, I couldn't get a response just now — try again in a moment.";
      addMessage('bot', reply);
      history.push({ role: 'assistant', content: reply });
    } catch (err) {
      typingEl.remove();
      addMessage('bot', "Hmm, I'm having trouble connecting. Please try again shortly.");
    } finally {
      sendBtn.disabled = false;
    }
  }

  function openPanel() {
    panel.classList.add('open');
    opened = true;
    if (messagesEl.children.length === 0) {
      addMessage('bot', "Hey! I'm the DropshippingStack assistant ⚡ I can help you find tools, compare options, or explain pricing. What are you working on?");
      addChips();
    }
    inputEl.focus();
  }

  bubble.addEventListener('click', () => {
    if (panel.classList.contains('open')) {
      panel.classList.remove('open');
    } else {
      openPanel();
    }
  });
  closeBtn.addEventListener('click', () => panel.classList.remove('open'));
  sendBtn.addEventListener('click', () => sendMessage());
  inputEl.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') sendMessage();
  });
})();