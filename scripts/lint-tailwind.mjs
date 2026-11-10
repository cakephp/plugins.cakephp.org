import { spawn } from 'node:child_process'
import { readFile, readdir } from 'node:fs/promises'
import { createRequire } from 'node:module'
import { resolve, relative } from 'node:path'
import { fileURLToPath, pathToFileURL } from 'node:url'

const root = fileURLToPath(new URL('../', import.meta.url))
const stylesheet = 'resources/css/style.css'
const require = createRequire(import.meta.url)
const server = spawn(process.execPath, [require.resolve('@tailwindcss/language-server/bin/tailwindcss-language-server'), '--stdio'], {
    cwd: root,
    stdio: 'pipe',
})
const workspace = { uri: pathToFileURL(root).href, name: 'plugins.cakephp.org' }
const settings = {
    editor: {},
    tailwindCSS: {
        includeLanguages: { php: 'html' },
        experimental: { configFile: { [stylesheet]: '**' } },
    },
}
let buffer = Buffer.alloc(0)
let sequence = 0
let finished = false
let initialized = false
let resolveProject
let resolveDiagnostics
let rejectRun
const projectReady = new Promise(resolve => { resolveProject = resolve })
const diagnosticsReady = new Promise(resolve => { resolveDiagnostics = resolve })
const failure = new Promise((_, reject) => { rejectRun = reject })
const pending = new Map()
const expected = new Set()
const diagnostics = new Map()
const send = message => {
    const body = Buffer.from(JSON.stringify({ jsonrpc: '2.0', ...message }))
    server.stdin.write(`Content-Length: ${body.length}\r\n\r\n`)
    server.stdin.write(body)
}
const request = (method, params) => new Promise((resolve, reject) => {
    const id = ++sequence
    pending.set(id, { resolve, reject })
    send({ id, method, params })
})
const notify = (method, params) => send({ method, params })

server.stdout.on('data', chunk => {
    buffer = Buffer.concat([buffer, chunk])
    while (true) {
        const boundary = buffer.indexOf('\r\n\r\n')
        if (boundary < 0) return
        const length = Number(/Content-Length:\s*(\d+)/i.exec(buffer.subarray(0, boundary).toString())[1])
        if (buffer.length < boundary + 4 + length) return
        const message = JSON.parse(buffer.subarray(boundary + 4, boundary + 4 + length).toString())
        buffer = buffer.subarray(boundary + 4 + length)
        if (message.method && message.id !== undefined) {
            let result = null
            if (message.method === 'workspace/configuration') {
                result = message.params.items.map(item => item.section?.split('.').reduce((value, key) => value?.[key], settings) ?? null)
            } else if (message.method === 'workspace/workspaceFolders') result = [workspace]
            send({ id: message.id, result })
        } else if (message.method === '@/tailwindCSS/projectInitialized') {
            initialized = true
            resolveProject()
        } else if (message.method === '@/tailwindCSS/error') rejectRun(new Error(message.params.message))
        else if (message.method === 'textDocument/publishDiagnostics' && initialized && expected.has(message.params.uri)) {
            diagnostics.set(message.params.uri, message.params.diagnostics)
            if (diagnostics.size === expected.size) resolveDiagnostics()
        } else if (pending.has(message.id)) {
            const promise = pending.get(message.id)
            pending.delete(message.id)
            if (message.error) promise.reject(new Error(message.error.message))
            else promise.resolve(message.result)
        }
    }
})
server.stderr.on('data', chunk => process.stderr.write(chunk))
server.on('error', rejectRun)
server.on('exit', code => {
    if (!finished) rejectRun(new Error(`Tailwind language server exited before completing lint (${code}).`))
})

async function sourceFiles(directory) {
    const files = []
    for (const entry of await readdir(resolve(root, directory), { withFileTypes: true })) {
        const path = `${directory}/${entry.name}`
        if (entry.isDirectory()) files.push(...await sourceFiles(path))
        else if (/\.(php|css|js)$/.test(entry.name)) files.push(path)
    }
    return files
}

async function lint() {
    const files = [
        ...await sourceFiles('templates'),
        ...await sourceFiles('resources'),
        'config/form-templates.php', 'config/html-templates.php', 'config/paginator-templates.php',
    ].sort()
    const documents = await Promise.all(files.map(async file => ({
        uri: pathToFileURL(resolve(root, file)).href,
        languageId: file.endsWith('.css') ? 'css' : file.endsWith('.js') ? 'javascript' : 'php',
        version: 1,
        text: await readFile(resolve(root, file), 'utf8'),
    })))
    await request('initialize', {
        processId: null,
        rootUri: workspace.uri,
        workspaceFolders: [workspace],
        capabilities: {
            workspace: { configuration: true, workspaceFolders: true },
            textDocument: { publishDiagnostics: { relatedInformation: true } },
        },
    })
    notify('initialized', {})
    // Project initialization publishes fresh diagnostics for every open document.
    for (const document of documents) expected.add(document.uri)
    for (const document of documents) notify('textDocument/didOpen', { textDocument: document })
    await projectReady
    await diagnosticsReady
    let count = 0
    for (const [uri, messages] of [...diagnostics].sort()) {
        for (const message of messages.filter(message => message.severity <= 2)) {
            const { line, character } = message.range.start
            console.error(`${relative(root, fileURLToPath(uri))}:${line + 1}:${character + 1} [${message.code}] ${message.message}`)
            count++
        }
    }
    console.log(`Tailwind: ${files.length} files checked, ${count} warnings/errors.`)
    process.exitCode = count ? 1 : 0
}

const timeout = setTimeout(() => rejectRun(new Error('Tailwind lint timed out before all source files were checked.')), 30000)
try {
    await Promise.race([lint(), failure])
} catch (error) {
    console.error(error.message)
    process.exitCode = 1
} finally {
    finished = true
    clearTimeout(timeout)
    server.kill()
}
