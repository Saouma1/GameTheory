// Note: this path assumes you've served socket.io properly on the server side
var socket = io.connect('http://localhost:5500');

function sendMessage() {
  var message = document.getElementById('m').value;
  socket.emit('chat message', message);
  document.getElementById('m').value = '';
  return false;
}

// This function is to append the incoming messages to the 'messages' list
socket.on('chat message', function(msg){
  var item = document.createElement('li');
  item.textContent = msg;
  document.getElementById('messages').appendChild(item);
  window.scrollTo(0, document.body.scrollHeight);
});
