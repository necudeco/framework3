function debug(){
	if ( typeof(console) == 'object' && typeof(console.log) == 'function' ){
		console.log(arguments);
	}
}
