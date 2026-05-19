<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Project 1: Real-time Chat</h2>
            <button onclick="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold">+ Chat Baru</button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg flex" style="height: 600px;">
                <div class="w-1/3 border-r p-4 overflow-y-auto">
                    <h3 class="font-bold mb-4 text-gray-600">Obrolan Anda</h3>
                    <ul class="space-y-2" id="conversation-list">
                        @foreach($conversations as $convo)
                            @php $recipient = $convo->users->where('id', '!=', auth()->id())->first(); @endphp
                            <li class="p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 flex justify-between items-center border" 
                                onclick="loadMessages({{ $convo->id }}, '{{ $convo->is_group ? $convo->name : ($recipient->name ?? 'User') }}', {{ $recipient->id ?? 0 }})">
                                <div>
                                    <p class="font-bold">{{ $convo->is_group ? $convo->name : ($recipient->name ?? 'User') }}</p>
                                    <small class="text-gray-400">{{ $convo->is_group ? 'Group' : 'Private' }}</small>
                                </div>
                                @if(!$convo->is_group && $recipient)
                                    <span id="status-{{ $recipient->id }}" class="w-3 h-3 rounded-full bg-gray-300 border"></span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="w-2/3 flex flex-col justify-between bg-gray-50">
                    <div class="p-4 bg-white border-b font-bold text-lg shadow-sm" id="chat-header">Pilih Obrolan</div>
                    <div id="chat-box" class="flex-1 overflow-y-auto p-4 space-y-4"></div>
                    <form onsubmit="sendMessage(event)" class="p-4 bg-white border-t flex gap-2">
                        <input type="hidden" id="current-convo-id">
                        <input type="text" id="msg-input" placeholder="Ketik pesan..." class="flex-1 border-gray-300 rounded-lg" disabled>
                        <button type="submit" id="send-btn" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold" disabled>Kirim</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-xl w-96">
            <h3 class="font-bold mb-4">Cari Orang</h3>
            <div id="modal-list" class="space-y-2"></div>
            <button onclick="closeModal()" class="mt-4 text-gray-500 text-sm w-full">Tutup</button>
        </div>
    </div>

    <script>
        let onlineUsers = [];

        // Presence Tracking (Fitur Papan Tulis poin 4)
        window.Echo.join('online')
            .here(users => { onlineUsers = users.map(u => u.id); updateStatusUI(); })
            .joining(user => { onlineUsers.push(user.id); updateStatusUI(); })
            .leaving(user => { onlineUsers = onlineUsers.filter(id => id !== user.id); updateStatusUI(); });

        function updateStatusUI() {
            document.querySelectorAll('[id^="status-"]').forEach(el => {
                const id = parseInt(el.id.replace('status-', ''));
                el.className = onlineUsers.includes(id) ? "w-3 h-3 rounded-full bg-green-500" : "w-3 h-3 rounded-full bg-gray-300";
            });
        }

        function openModal() {
            document.getElementById('userModal').classList.remove('hidden');
            fetch('/chat/users').then(r => r.json()).then(users => {
                const list = document.getElementById('modal-list');
                list.innerHTML = '';
                users.forEach(u => {
                    list.innerHTML += `<form action="/chat/create/${u.id}" method="POST">@csrf<button class="w-full text-left p-2 border-b hover:bg-gray-50">${u.name}</button></form>`;
                });
            });
        }

        function closeModal() { document.getElementById('userModal').classList.add('hidden'); }

        function loadMessages(id, name, rId) {
            document.getElementById('current-convo-id').value = id;
            document.getElementById('msg-input').disabled = false;
            document.getElementById('send-btn').disabled = false;
            document.getElementById('chat-header').innerText = name;
            
            fetch(`/chat/${id}`).then(r => r.json()).then(msgs => {
                const box = document.getElementById('chat-box');
                box.innerHTML = '';
                msgs.forEach(m => appendMsg(m));
                box.scrollTop = box.scrollHeight;
                connectWebsocket(id);
            });
        }

        function appendMsg(m) {
            const isMe = m.user_id == "{{ auth()->id() }}";
            const html = `<div class="flex ${isMe ? 'justify-end' : 'justify-start'}">
                <div class="p-2 rounded-lg ${isMe ? 'bg-blue-500 text-white' : 'bg-gray-200'}">
                    <small class="block font-bold text-xs">${m.user.name}</small>
                    <p>${m.body}</p>
                </div>
            </div>`;
            document.getElementById('chat-box').insertAdjacentHTML('beforeend', html);
            document.getElementById('chat-box').scrollTop = document.getElementById('chat-box').scrollHeight;
        }

        function sendMessage(e) {
            e.preventDefault();
            const id = document.getElementById('current-convo-id').value;
            const body = document.getElementById('msg-input').value;
            fetch(`/chat/${id}/messages`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({body})
            }).then(r => r.json()).then(m => {
                document.getElementById('msg-input').value = '';
                appendMsg(m);
            });
        }

        function connectWebsocket(id) {
            window.Echo.private(`chat.${id}`).listen('MessageSent', e => appendMsg(e.message));
        }
    </script>
</x-app-layout>