import { WebSocketServer } from 'ws';

// Khởi tạo server chạy ở port 8080
const wss = new WebSocketServer({ port: 8080 });

console.log('🚀 WebSocket Server đang khởi động tại ws://localhost:8080');

wss.on('connection', function connection(ws) {
    console.log('🟢 Một Client (Frontend) vừa kết nối!');

    // Lắng nghe tin nhắn từ Frontend gửi lên
    ws.on('message', function incoming(message) {
        const data = message.toString();
        console.log('Nhận được dữ liệu update:', data);

        // Phát (Broadcast) dữ liệu này tới TẤT CẢ các client khác đang kết nối
        wss.clients.forEach(function each(client) {
            // Trạng thái 1 tương đương với WebSocket.OPEN (Đang kết nối)
            if (client !== ws && client.readyState === 1) {
                client.send(data);
            }
        });
    });

    ws.on('close', () => {
        console.log('🔴 Một Client đã ngắt kết nối');
    });
});