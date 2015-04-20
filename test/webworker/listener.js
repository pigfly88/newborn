function messageHandler(e){
	postMessage('woker says: '+ e.data);
}
addEventListener('message', messageHandler, true);