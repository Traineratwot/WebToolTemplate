"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const ws_1 = require("ws");
const fs = __importStar(require("fs"));
const path = __importStar(require("path"));
const chokidar_1 = __importDefault(require("chokidar"));
const model = process.argv[2].split('=')[1];
const base = process.argv[3].split('=')[1];
const lock = path.join(model, 'tools', 'devServer.lock');
process.on('SIGINT', () => {
    console.log('Received SIGINT. Press Control-C to exit.');
    if (fs.existsSync(lock)) {
        fs.unlinkSync(lock);
    }
    process.exit(1);
});
if (!fs.existsSync(lock)) {
    fs.open(lock, 'w', (err) => {
        if (err)
            throw err;
    });
}
const users = new Set();
const wss = new ws_1.WebSocketServer({
    port: 8080,
    perMessageDeflate: {
        zlibDeflateOptions: {
            // See zlib defaults.
            chunkSize: 1024,
            memLevel: 7,
            level: 3
        },
        zlibInflateOptions: {
            chunkSize: 10 * 1024
        },
        // Other options settable:
        clientNoContextTakeover: true,
        serverNoContextTakeover: true,
        serverMaxWindowBits: 10,
        // Below options specified as default values.
        concurrencyLimit: 10,
        threshold: 1024 // Size (in bytes) below which messages
        // should not be compressed if context takeover is disabled.
    }
});
wss.on('connection', (ws) => {
    users.add(ws);
    ws.on('close', () => {
        users.delete(ws);
    });
});
function sendReload(path) {
    console.log(path);
    users.forEach((ws) => {
        ws.send('reload');
    });
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
console.log(watch);
const watcher = chokidar_1.default.watch(watch, {
    ignored: /((^|[\/\\])\..)|(.*[\/\\]cache[\/\\].*)/,
    persistent: true,
    ignoreInitial: true,
    followSymlinks: false,
    ignorePermissionErrors: true,
});
// Something to use when events are received.
const log = console.log.bind(console);
// Add event listeners.
watcher
    .on('add', sendReload)
    .on('change', sendReload)
    .on('unlink', sendReload);
