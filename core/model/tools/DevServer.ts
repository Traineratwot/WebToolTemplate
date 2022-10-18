import {WebSocket, WebSocketServer} from 'ws';
import * as fs                      from "fs";
import * as path                    from "path";
import chokidar                     from 'chokidar';

const model: string = process.argv[2].split('=')[1];
const base: string  = process.argv[3].split('=')[1];
const lock          = path.join(model, 'tools', 'devServer.lock')
process.on('SIGINT', () => {
	console.log('Received SIGINT. Press Control-C to exit.');
	if (fs.existsSync(lock)) {
		fs.unlinkSync(lock)
	}
	process.exit(1);
});
if (!fs.existsSync(lock)) {
	fs.open(lock, 'w', (err) => {
		if (err) throw err;
	});
}
const users = new Set<WebSocket>()
const wss   = new WebSocketServer(
	{
		port             : 8080,
		perMessageDeflate: {
			zlibDeflateOptions: {
				// See zlib defaults.
				chunkSize: 1024,
				memLevel : 7,
				level    : 3
			},
			zlibInflateOptions: {
				chunkSize: 10 * 1024
			},
			// Other options settable:
			clientNoContextTakeover: true, // Defaults to negotiated value.
			serverNoContextTakeover: true, // Defaults to negotiated value.
			serverMaxWindowBits    : 10, // Defaults to negotiated value.
			// Below options specified as default values.
			concurrencyLimit: 10, // Limits zlib concurrency for perf.
			threshold       : 1024 // Size (in bytes) below which messages
			// should not be compressed if context takeover is disabled.
		}
	}
);

wss.on('connection', (ws) => {
	users.add(ws);
	ws.on('close', () => {
		users.delete(ws);
	})
});

function sendReload(path: string) {
	console.log(path)
	users.forEach((ws) => {
		ws.send('reload')
	})
}

// Initialize watcher.
const watch = [
	base + '*.js',
	base + '*.php',
	base + '*.tpl',

	base + path.join('**', '*.js'),
	base + path.join('**', '*.php'),
	base + path.join('**', '*.tpl'),
];
console.log(watch)
const watcher = chokidar.watch(watch
	, {
								   ignored               : /((^|[\/\\])\..)|(.*[\/\\]cache[\/\\].*)/, // ignore dotfiles
								   persistent            : true,
								   ignoreInitial         : true,
								   followSymlinks        : false,
								   ignorePermissionErrors: true,
							   });

// Something to use when events are received.
const log = console.log.bind(console);
// Add event listeners.
watcher
	.on('add', sendReload)
	.on('change', sendReload)
	.on('unlink', sendReload);
