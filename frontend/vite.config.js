import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

export default defineConfig(({ command }) => {
  const isBuild = command === 'build'

  return {
    plugins: [vue()],
    base: isBuild ? '/spa/' : '/',
    build: isBuild
      ? {
          outDir: path.resolve(__dirname, '../backend/public/spa'),
          emptyOutDir: true,
        }
      : undefined,
    server: {
      proxy: {
        '/api': {
          target: 'http://127.0.0.1:8000',
          changeOrigin: true,
        },
      },
    },
  }
})
