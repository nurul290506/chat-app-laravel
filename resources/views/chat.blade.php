<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Project 1: Real-time Chat</h2>
            <button onclick="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-blue-700 transition">+ Chat Baru</button>
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
                            <li class="p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 flex justify-between items-center border transition" 
                                onclick="loadMessages({{ $convo->id }}, '{{ $convo->is_group ? $convo->name : ($recipient->name ?? 'User') }}', {{ $recipient->id ?? 0 }})">
                                <div>
                                    <p class="font-bold text-gray-700">{{ $convo->is_group ? $convo->name : ($recipient->name ?? 'User') }}</p>
                                    <small class="text-gray-400 font-medium">{{ $convo->is_group ? 'Group' : 'Private' }}</small>
                                </div>
                                @if(!$convo->is_group && $recipient)
                                    <span id="status-{{ $recipient->id }}" class="w-3 h-3 rounded-full bg-gray-300 border shadow-sm transition-colors duration-300"></span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="w-2/3 flex flex-col justify-between bg-gray-50">
                    <div class="p-4 bg-white border-b font-bold text-lg shadow-sm text-gray-700 shadow-sm" id="chat-header">Pilih Obrolan</div>
                    <div id="chat-box" class="flex-1 overflow-y-auto p-4 space-y-4">
                        <div class="text-center text-gray-400 mt-20">Silakan pilih room obrolan di sebelah kiri atau buat chat baru untuk memulai pesan.</div>
                    </div>
                    <form onsubmit="sendMessage(event)" class="p-4 bg-white border-t flex gap-2">
                        <input type="hidden" id="current-convo-id">
                        <input type="text" id="msg-input" placeholder="Ketik pesan..." class="flex-1 border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500" disabled>
                        <button type="submit" id="send-btn" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold shadow hover:bg-blue-700 transition disabled:opacity-50" disabled>Kirim</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 animate-fade-in">
        <div class="bg-white p-6 rounded-xl w-96 shadow-xl">
            <h3 class="font-bold mb-4 text-gray-700 text-lg border-b pb-2">Cari Orang</h3>
            <div id="modal-list" class="space-y-2 max-height-[300px] overflow-y-auto"></div>
            <button onclick="closeModal()" class="mt-4 text-gray-500 hover:text-red-500 font-semibold text-sm w-full text-center block pt-2 border-t">Tutup</button>
        </div>
    </div>

    <script>
        let onlineUsers = [];
        let activeChannelId = null;

        function updateStatusUI() {
            onlineUsers.forEach(id => {
                const el = document.getElementById(`status-${id}`);
                if(el) {
                    el.classList.remove('bg-gray-300');
                    el.style.backgroundColor = '#22c55e';
                }
            });
            // Tandai user yang offline
            document.querySelectorAll('[id^="status-"]').forEach(el => {
                const id = parseInt(el.id.replace('status-', ''));
                if (!onlineUsers.includes(id)) {
                    el.style.backgroundColor = '#d1d5db';
                    el.classList.add('bg-gray-300');
                }
            });
        }

        // 1. Pelacak Status Online (Presence Channel)
        document.addEventListener('DOMContentLoaded', () => {

            const waitForEcho = setInterval(() => {

             if (window.Echo) {
                clearInterval(waitForEcho);
                console.log('Echo ditemukan, menghubungkan ke presence channel...');
                window.Echo.join('online')
                    .here(users => {
                        console.log('User online:', users);

                        onlineUsers = users.map(u => u.id);
                        updateStatusUI();
                })
                .joining(user => {
                    console.log('User masuk:', user);
                    if (!onlineUsers.includes(user.id)) {
                        onlineUsers.push(user.id);
                    }
                    updateStatusUI();
                })
                .leaving(user => {
                    console.log('User keluar:', user);
                    onlineUsers = onlineUsers.filter(id => id !== user.id);
                    updateStatusUI();
                });

        }

    }, 100);

});

        // 2. Logika Modal Cari Orang (Sudah Diperbaiki Menggunakan Tombol Submit)
        function openModal() {
            document.getElementById('userModal').classList.remove('hidden');
            fetch('/chat/users').then(r => r.json()).then(users => {
                const list = document.getElementById('modal-list');
                list.innerHTML = '';
                if(users.length === 0) {
                    list.innerHTML = '<p class="text-gray-400 text-center text-sm p-4">Tidak ada user lain tersedia</p>';
                    return;
                }
                users.forEach(u => {
                    list.innerHTML += `
                        <form action="/chat/create/${u.id}" method="POST" class="m-0">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="w-full text-left p-3 border rounded-lg hover:bg-blue-50 hover:text-blue-600 transition font-medium block mb-1">
                                👦 ${u.name}
                            </button>
                        </form>
                    `;
                });
            });
        }

        function closeModal() { document.getElementById('userModal').classList.add('hidden'); }

        // 3. Mengambil Riwayat Pesan Lama via AJAX
        function loadMessages(id, name, rId) {
            document.getElementById('current-convo-id').value = id;
            document.getElementById('msg-input').disabled = false;
            document.getElementById('send-btn').disabled = false;
            document.getElementById('chat-header').innerText = name;
            document.getElementById('msg-input').focus();
            
            fetch(`/chat/${id}`).then(r => r.json()).then(msgs => {
                const box = document.getElementById('chat-box');
                box.innerHTML = '';
                if(msgs.length === 0) {
                    box.innerHTML = '<div class="text-center text-gray-400 mt-10 text-sm">Belum ada obrolan. Kirim pesan pertama Anda!</div>';
                } else {
                    msgs.forEach(m => appendMsg(m));
                }
                box.scrollTop = box.scrollHeight;
                connectWebsocket(id);
            });
        }

        // 4. Memasukkan Balon Chat ke Layar Browser
        function appendMsg(m) {
            // Bersihkan teks panduan jika masih ada
            const box = document.getElementById('chat-box');
            if (box.querySelector('.text-center')) {
                box.innerHTML = '';
            }

            const isMe = m.user_id == "{{ auth()->id() }}";
            const html = `<div class="flex ${isMe ? 'justify-end' : 'justify-start'} animate-fade-in">
                <div class="max-w-md p-3 rounded-xl shadow-sm ${isMe ? 'bg-blue-500 text-white rounded-tr-none' : 'bg-white text-gray-800 border rounded-tl-none'}">
                    <small class="block font-bold text-xs ${isMe ? 'text-blue-100' : 'text-gray-500'} mb-1">${m.user.name}</small>
                    <p class="text-sm leading-relaxed break-words">${m.body}</p>
                </div>
            </div>`;
            box.insertAdjacentHTML('beforeend', html);
            box.scrollTop = box.scrollHeight;
        }

        // 5. Mengirim Pesan Baru via AJAX POST
        function sendMessage(e) {
            e.preventDefault();
            const id = document.getElementById('current-convo-id').value;
            const input = document.getElementById('msg-input');
            const body = input.value.trim();
            
            if(!body) return; // Mencegah kirim pesan kosong
            
            fetch(`/chat/${id}/messages`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({body})
            }).then(r => r.json()).then(m => {
                input.value = '';
                appendMsg(m);
            });
        }

        // 6. Menghubungkan Kurir WebSocket Real-time (Private Channel)
        function connectWebsocket(id) {
            // Putuskan koneksi room lama terlebih dahulu jika user berpindah room chat
            if (activeChannelId && activeChannelId !== id) {
                window.Echo.leave(`chat.${activeChannelId}`);
            }
            activeChannelId = id;

            window.Echo.private(`chat.${id}`)
                .listen('MessageSent', e => {
                    // Hanya append pesan jika dikirim oleh orang lain (mencegah double render)
                    if(e.message.user_id != "{{ auth()->id() }}") {
                        appendMsg(e.message);
                    }
                });
        }
    </script>
</x-app-layout>