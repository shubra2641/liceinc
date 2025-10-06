module.exports = {
  ignorePatterns: [
    'vendor/**',
    'node_modules/**',
    'public/assets/vendor/**',
    '*.min.js',
    '*.min.css',
    'public/assets/front/js/front-consolidated.js',
    'public/assets/js/chart.js',
    '**/chart.js',
    '**/front-consolidated.js'
  ],
  env: {
    browser: true,
    es2021: true,
    node: true
  },
  globals: {
    window: 'readonly',
    document: 'readonly',
    console: 'readonly',
    URL: 'readonly',
    URLSearchParams: 'readonly',
    fetch: 'readonly',
    setTimeout: 'readonly',
    clearTimeout: 'readonly',
    setInterval: 'readonly',
    clearInterval: 'readonly',
    localStorage: 'readonly',
    sessionStorage: 'readonly',
    navigator: 'readonly',
    location: 'readonly',
    FormData: 'readonly'
  },
  extends: [
    'standard'
  ],
  parserOptions: {
    ecmaVersion: 'latest',
    sourceType: 'module'
  },
  overrides: [
    {
      files: ['.eslintrc.js'],
      env: {
        node: true,
        browser: false
      },
      globals: {
        module: 'readonly',
        require: 'readonly',
        process: 'readonly',
        __dirname: 'readonly'
      }
    },
    {
      files: ['webpack.mix.js'],
      env: {
        node: true,
        browser: false
      },
      globals: {
        require: 'readonly',
        module: 'readonly',
        process: 'readonly',
        __dirname: 'readonly'
      }
    },
    {
      files: ['**/admin-charts.js'],
      env: {
        browser: true,
        node: false
      },
      globals: {
        Chart: 'readonly',
        window: 'readonly',
        document: 'readonly',
        console: 'readonly',
        URL: 'readonly',
        fetch: 'readonly',
        setTimeout: 'readonly',
        setInterval: 'readonly',
        clearTimeout: 'readonly',
        clearInterval: 'readonly',
        Blob: 'readonly',
        MutationObserver: 'readonly'
      }
    }
  ],
  rules: {
    'no-console': 'off',
    'no-debugger': 'off',
    'no-unused-vars': 'error',
    'no-undef': 'error',
    'semi': ['error', 'always'],
    'quotes': ['error', 'single'],
    'indent': ['error', 2],
    'comma-dangle': ['error', 'always-multiline'],
    'space-before-function-paren': ['error', 'never'],
    'object-curly-spacing': ['error', 'always'],
    'array-bracket-spacing': ['error', 'never'],
    'key-spacing': ['error', { beforeColon: false, afterColon: true }],
    'keyword-spacing': ['error', { before: true, after: true }],
    'space-infix-ops': 'error',
    'eqeqeq': ['error', 'always'],
    'curly': ['error', 'all'],
    'brace-style': ['error', '1tbs'],
    'no-trailing-spaces': 'error',
    'eol-last': 'error',
    'no-multiple-empty-lines': ['error', { max: 1 }],
    'padded-blocks': ['error', 'never'],
    'comma-spacing': ['error', { before: false, after: true }],
    'func-call-spacing': ['error', 'never'],
    'no-floating-decimal': 'error',
    'no-multi-spaces': 'error',
    'operator-linebreak': ['error', 'after'],
    'block-spacing': 'error',
    'arrow-spacing': 'error',
    'template-curly-spacing': 'error',
    'rest-spread-spacing': 'error',
    'yield-star-spacing': 'error',
    'generator-star-spacing': 'error',
    'space-unary-ops': 'error',
    'space-in-parens': ['error', 'never'],
    'computed-property-spacing': ['error', 'never'],
    'func-name-matching': 'error',
    'consistent-return': 'error',
    'no-else-return': 'error',
    'no-lonely-if': 'error',
    'no-unneeded-ternary': 'error',
    'no-useless-return': 'error',
    'prefer-const': 'error',
    'no-var': 'error',
    'prefer-arrow-callback': 'error',
    'arrow-parens': ['error', 'as-needed'],
    'arrow-body-style': ['error', 'as-needed'],
    'object-shorthand': 'error',
    'prefer-template': 'error',
    'template-tag-spacing': 'error',
    'no-useless-concat': 'error',
    'no-useless-escape': 'error',
    'prefer-destructuring': 'error',
    'no-duplicate-imports': 'error',
    'import/order': ['error', { 'groups': ['builtin', 'external', 'internal', 'parent', 'sibling', 'index'] }],
    'import/newline-after-import': 'error',
    'import/no-unresolved': 'off',
    'import/no-absolute-path': 'error',
    'import/no-dynamic-require': 'error',
    'import/no-webpack-loader-syntax': 'error',
    'promise/always-return': 'error',
    'promise/no-return-wrap': 'error',
    'promise/param-names': 'error',
    'promise/catch-or-return': 'error',
    'promise/no-native': 'off',
    'promise/no-nesting': 'error',
    'promise/no-promise-in-callback': 'error',
    'promise/no-callback-in-promise': 'error',
    'promise/avoid-new': 'off',
    'promise/no-new-statics': 'error',
    'promise/no-return-in-finally': 'error',
    'promise/valid-params': 'error'
  }
};