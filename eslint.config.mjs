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
  },
  {
    files: ['wp-content/themes/mba-menuiseries/assets/js/*.js'],
    languageOptions: {
      globals: { document: 'readonly', window: 'readonly', ResizeObserver: 'readonly' }
    }
  },
  {
    files: ['wp-content/plugins/mba-site-core/assets/js/*.js'],
    languageOptions: {
      globals: {
        document: 'readonly',
        jQuery: 'readonly',
        wp: 'readonly',
        mbaProductAdmin: 'readonly',
        mbaProjectAdmin: 'readonly',
        mbaEntryAdmin: 'readonly'
      }
    }
  }
];
