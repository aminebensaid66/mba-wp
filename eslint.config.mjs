import js from '@eslint/js';

export default [
  {
    ignores: ['vendor/**', 'node_modules/**', 'wordpress/**', 'wp-content/uploads/**']
  },
  js.configs.recommended,
  {
    files: ['**/*.mjs', '**/*.js'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        console: 'readonly'
      }
    }
  }
];
