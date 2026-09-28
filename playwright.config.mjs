import { defineConfig, devices } from '@playwright/test';
import process from 'node:process';

const projects = [
  { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
  { name: 'firefox', use: { ...devices['Desktop Firefox'] } },
  { name: 'webkit', use: { ...devices['Desktop Safari'] } },
];

if (process.env.E2E_INCLUDE_EDGE === '1') {
  projects.push({ name: 'edge', use: { ...devices['Desktop Edge'], channel: 'msedge' } });
}

export default defineConfig({
  testDir: './tests/e2e',
  testMatch: '*.spec.js',
  timeout: 60000,
  fullyParallel: false,
  workers: 1,
  forbidOnly: Boolean(process.env.CI),
  retries: process.env.CI ? 1 : 0,
  reporter: [['list'], ['html', { outputFolder: 'playwright-report', open: 'never' }]],
  use: {
    baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:18081',
    extraHTTPHeaders: { 'X-MBA-E2E-Test': '1' },
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects,
});
