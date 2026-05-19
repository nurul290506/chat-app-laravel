<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Simulasi Chat Real-time') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex" style="height: 500px;">
                
                <div class="w-1/3 border-r border-gray-200 p-4 overflow-y-auto">
                    <h3 class="font-bold mb-3 text-gray-700">Daftar Obrolan</h3>
                    <ul>
                        @foreach($conversations as $convo)
                            <li class="p-3 mb-2 bg-gray-100 rounded cursor-pointer hover:bg-gray-200" 
                                onclick="loadMessages({{ $convo->id }})">
                                {{ $convo->is_group ? '[Grup] ' . $convo->name : 'Chat Privat ID: ' . $convo->id }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="w-2/3 flex flex-col justify-between p-6 bg-gray-50">
                    <div id="chat-box" class="flex-1 overflow-y-auto mb-4 p-4 bg-white border rounded space-y-3">
                        <p class="text-gray-400 text-center">Silakan klik salah satu obrolan di sebelah kiri.</p>
                    </div>

                    <form id="chat-form" onsubmit="sendMessage(event)" class="flex gap-2">
                        <input type="hidden" id="current-conversation-id">
                        <input type="text" id="message-input" placeholder="Ketik pesan di sini..." 
                               class="flex-1 border-gray-300 rounded-lg" disabled>
                        <button type="submit" id="send-button" class="bg-blue-600 text-white px-4 py-2 rounded-lg" disabled>
                            Kirim
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        let activeChannel = null;

        // 1. Fungsi mengambil pesan dari database via AJAX
        function loadMessages(conversationId) {
            document.getElementById('current-conversation-id').value = conversationId;
            document.getElementById('message-input').disabled = false;
            document.getElementById('send-button').disabled = false;

            fetch(`/chat/${conversationId}`)
                .then(res => res.json())
                .then(messages => {
                    const chatBox = document.getElementById('chat-box');
                    chatBox.innerHTML = ''; // Kosongkan text bantuan
                    
                    messages.forEach(msg => {
                        appendMessage(msg);
                    });
                    chatBox.scrollTop = chatBox.scrollHeight;

                    // Sambungkan ke WebSocket untuk room ini
                    connectWebSocket(conversationId);
                });
        }

        // 2. Fungsi menempelkan bubble chat ke layar
        function appendMessage(msg) {
            const chatBox = document.getElementById('chat-box');
            const isMe = msg.user_id == "{{ auth()->id() }}";
            
            const msgHtml = `
                <div class="flex ${isMe ? 'justify-end' : 'justify-start'}">
                    <div class="p-2 rounded-lg max-w-xs ${isMe ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'}">
                        <small class="block text-xs font-bold">${msg.user.name}</small>
                        <p>${msg.body}</p>
                    </div>
                </div>
            `;
            chatBox.insertAdjacentHTML('beforeend', msgHtml);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // 3. Fungsi mengirim pesan ke server (AJAX POST)
        function sendMessage(event) {
            event.preventDefault();
            const convoId = document.getElementById('current-conversation-id').value;
            const input = document.getElementById('message-input');
            const body = input.value;

            if(!body.trim()) return;

            fetch(`/chat/${convoId}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ body: body })
            })
            .then(res => res.json())
            .then(message => {
                input.value = '';
                appendMessage(message); // Munculkan di layar kita sendiri
            });
        }

        // 4. Fungsi mendengarkan WebSocket (Laravel Echo)
        function connectWebSocket(conversationId) {
            if (activeChannel) {
                window.Echo.leave(`chat.${activeChannel}`);
            }

            activeChannel = conversationId;

            // Mendengarkan event 'MessageSent' secara real-time
            window.Echo.private(`chat.${conversationId}`)
                .listen('MessageSent', (e) => {
                    appendMessage(e.message); // Otomatis muncul di layar lawan bicara!
                });
        }
    </script>
</x-app-layout>