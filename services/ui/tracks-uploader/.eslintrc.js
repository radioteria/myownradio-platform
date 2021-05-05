module.exports = {
  parser: '@typescript-eslint/parser',
  parserOptions: {
    ecmaVersion: 2018,
    sourceType: 'module',
    ecmaFeatures: {
      jsx: true,
    },
    warnOnUnsupportedTypeScriptVersion: true,
  },
  extends: [
    'plugin:react/recommended',
    'plugin:@typescript-eslint/recommended',
    'plugin:prettier/recommended',
    'plugin:clean-regex/recommended',
    'plugin:sonarjs/recommended',
    'plugin:promise/recommended',
  ],
  plugins: [
    '@typescript-eslint',
    'import',
    'jsx-a11y',
    'react',
    'react-hooks',
    'clean-regex',
    'sonarjs',
    'promise',
  ],
  settings: {
    react: {
      version: 'detect',
    },
  },
  root: true,
}
