import { globalIgnores } from 'eslint/config';
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';
import pluginVue from 'eslint-plugin-vue';
import skipFormatting from '@vue/eslint-config-prettier/skip-formatting';

export default defineConfigWithVueTs(
    {
        name: 'app/files-to-lint',
        files: ['resources/js/**/*.{ts,mts,tsx,vue}'],
    },
    globalIgnores(['**/dist/**', '**/node_modules/**', '**/vendor/**', '**/public/**', '**/storage/**', '**/bootstrap/cache/**']),
    pluginVue.configs['flat/essential'],
    vueTsConfigs.recommended,
    skipFormatting,
    {
        // shadcn-vue primitives (Button, Card, Input, …) and reusable building blocks
        // are intentionally single-word.
        name: 'app/ui-component-names',
        files: ['resources/js/components/**/*.vue'],
        rules: {
            'vue/multi-word-component-names': 'off',
        },
    },
);
