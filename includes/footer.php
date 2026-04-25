</main>
<?php if(isset($_SESSION['user_id'])): ?>
<!-- Chat Widget UI -->
<div id="chat-widget" class="fixed bottom-6 right-6 z-50">
    <button id="chat-toggle" class="bg-emerald-500 hover:bg-emerald-600 text-slate-900 rounded-full h-14 w-14 flex items-center justify-center shadow-lg shadow-emerald-500/30 transition-transform hover:scale-105">
        <i class="ph-fill ph-chat-circle-dots text-3xl"></i>
    </button>
</div>

<div id="chat-popover" class="fixed bottom-24 right-6 w-full max-w-[800px] h-[500px] bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 z-50 flex overflow-hidden hidden transition-all origin-bottom-right" style="width: calc(100vw - 48px);">
    <!-- Left Sidebar: Chat List -->
    <div class="w-1/3 min-w-[200px] border-r border-slate-700 bg-slate-850 flex flex-col h-full">
        <div class="p-4 border-b border-slate-700 bg-slate-800">
            <h3 class="font-bold text-white flex items-center gap-2"><i class="ph-fill ph-envelope-simple text-emerald-500 mt-0.5"></i> Pesan</h3>
        </div>
        <div id="chat-list" class="flex-grow overflow-y-auto">
            <!-- Rendered by JS -->
        </div>
    </div>
    
    <!-- Right Area: Messages -->
    <div class="w-2/3 flex-grow bg-slate-900 flex flex-col h-full relative" id="chat-main-area">
        <div class="flex-grow flex items-center justify-center flex-col text-slate-500" id="chat-empty-state">
            <i class="ph-fill ph-chats text-6xl mb-4 text-slate-700"></i>
            <p class="text-sm">Pilih pesan di samping untuk mulai chat</p>
        </div>
        
        <div id="chat-room" class="hidden flex flex-col h-full w-full">
            <div class="h-14 border-b border-slate-700 flex items-center justify-between px-4 bg-slate-800 flex-shrink-0">
                <h4 class="font-bold text-white text-sm truncate pr-2" id="chat-room-title">TRX Title</h4>
                <a href="#" id="chat-go-escrow" class="text-[10px] font-bold bg-emerald-500 text-slate-900 px-3 py-1.5 rounded-full hover:bg-emerald-600 transition whitespace-nowrap">Buka Escrow</a>
            </div>
            <div class="flex-grow overflow-y-auto p-4 space-y-3 bg-[#0b1120] chat-scroll" id="chat-messages">
                <!-- Rendered by JS -->
            </div>
            <div class="p-3 bg-slate-800 border-t border-slate-700">
                <form id="chat-form" class="flex gap-2">
                    <input type="text" id="chat-input" class="flex-grow bg-slate-900 border border-slate-600 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500 text-sm" placeholder="Ketik pesan..." required autocomplete="off">
                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-slate-900 px-4 py-2 rounded-xl font-bold transition"><i class="ph-fill ph-paper-plane-right text-lg"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const chatToggle = document.getElementById('chat-toggle');
    const chatPopover = document.getElementById('chat-popover');
    const chatList = document.getElementById('chat-list');
    const chatEmpty = document.getElementById('chat-empty-state');
    const chatRoom = document.getElementById('chat-room');
    const chatMessages = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatTitle = document.getElementById('chat-room-title');
    const chatGoEscrow = document.getElementById('chat-go-escrow');
    
    let activeTrxId = null;
    let pollInterval = null;
    const currentUserRole = '<?= $_SESSION['role'] ?? '' ?>';

    if(chatToggle) {
        chatToggle.addEventListener('click', () => {
            chatPopover.classList.toggle('hidden');
            if(!chatPopover.classList.contains('hidden')) {
                loadChatList();
            } else {
                clearInterval(pollInterval);
            }
        });
    }

    async function loadChatList() {
        try {
            const res = await fetch('/tavernex/pages/chat_api.php?action=get_list');
            const data = await res.json();
            if(data.status === 'success') {
                chatList.innerHTML = '';
                if(data.data.length === 0) {
                    chatList.innerHTML = '<p class="text-center text-xs text-slate-500 mt-6">Tidak ada pesan.</p>';
                    return;
                }
                data.data.forEach(chat => {
                    const div = document.createElement('div');
                    div.className = 'p-3 border-b border-slate-700/50 hover:bg-slate-800 cursor-pointer transition flex items-center gap-3';
                    if(activeTrxId === chat.trx_id) div.classList.add('bg-slate-700');
                    div.innerHTML = `
                        <div class="h-10 w-10 min-w-10 rounded-full bg-slate-700 border border-slate-600 text-emerald-400 flex items-center justify-center font-bold text-lg">${chat.title.charAt(0)}</div>
                        <div class="overflow-hidden flex-grow">
                            <h5 class="text-sm font-bold text-white truncate">${chat.title}</h5>
                            <p class="text-[11px] text-slate-400 truncate mt-0.5">${chat.desc}</p>
                        </div>
                    `;
                    div.addEventListener('click', () => {
                        openChat(chat.trx_id, chat.title);
                        document.querySelectorAll('#chat-list > div').forEach(el => el.classList.remove('bg-slate-700'));
                        div.classList.add('bg-slate-700');
                    });
                    chatList.appendChild(div);
                });
            }
        } catch (e) { console.error(e); }
    }

    async function openChat(trx_id, title) {
        activeTrxId = trx_id;
        chatTitle.innerText = title;
        chatGoEscrow.href = '/tavernex/pages/escrow.php?id=' + trx_id;
        chatEmpty.classList.add('hidden');
        chatRoom.classList.remove('hidden');
        
        await loadMessages();
        clearInterval(pollInterval);
        pollInterval = setInterval(loadMessages, 3000);
    }

    async function loadMessages() {
        if(!activeTrxId) return;
        try {
            const res = await fetch('/tavernex/pages/chat_api.php?action=get_messages&trx_id='+activeTrxId);
            const data = await res.json();
            if(data.status === 'success') {
                let shouldScroll = chatMessages.scrollTop + chatMessages.clientHeight === chatMessages.scrollHeight;
                if(chatMessages.innerHTML === '') shouldScroll = true;

                let html = '';
                data.data.forEach(msg => {
                    let isMe = (msg.sender_role === currentUserRole);
                    if(msg.sender_role === 'system') {
                        html += `<div class="flex justify-center my-2"><span class="bg-slate-800 border border-slate-700 text-slate-400 text-[10px] px-3 py-1.5 rounded-lg text-center whitespace-pre-wrap max-w-[85%]">${msg.message}</span></div>`;
                    } else {
                        let align = isMe ? 'justify-end' : 'justify-start';
                        let bubbleStr = isMe ? 'bg-emerald-600 text-white rounded-br-none' : (msg.sender_role == 'admin' ? 'bg-slate-700 text-white border border-slate-600 rounded-bl-none' : 'bg-slate-800 text-slate-200 border border-slate-700 rounded-bl-none');
                        html += `
                            <div class="flex w-full ${align}">
                                <div class="max-w-[85%] flex flex-col ${isMe ? 'items-end' : 'items-start'}">
                                    ${!isMe ? `<div class="text-[9px] font-bold mb-1 ml-1 text-slate-400">${msg.sender_name} <span class="uppercase">(${msg.sender_role})</span></div>` : ''}
                                    <div class="p-3 rounded-2xl ${bubbleStr} text-xs whitespace-pre-wrap leading-relaxed">${msg.message.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</div>
                                    <div class="text-[9px] text-slate-500 mt-1 mx-1">${msg.time}</div>
                                </div>
                            </div>
                        `;
                    }
                });
                chatMessages.innerHTML = html;
                if(shouldScroll) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }
        } catch (e) { console.error(e); }
    }

    if(chatForm) {
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const msg = chatInput.value;
            if(!msg || !activeTrxId) return;
            chatInput.value = '';
            
            try {
                const res = await fetch('/tavernex/pages/chat_api.php?action=send_message', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({trx_id: activeTrxId, message: msg})
                });
                const data = await res.json();
                if(data.status === 'success') {
                    loadMessages();
                }
            } catch (e) { console.error(e); }
        });
    }
});
</script>
<?php endif; ?>
</body>
</html>