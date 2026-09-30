<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    nodes: { type: Array, default: () => [] },
});

const emit = defineEmits(['select-agent']);
const selectedId = ref(null);

const WIDTH  = 560;
const HEIGHT = 380;
const CX     = WIDTH  / 2;
const CY     = HEIGHT / 2;
const R      = 155;

const maxTokens = computed(() => Math.max(1, ...props.nodes.map(n => n.total_tokens)));

function nodeRadius(tokens) {
    const min = 14, max = 34;
    if (tokens === 0) return min;
    return min + ((tokens / maxTokens.value) * (max - min));
}

const positioned = computed(() => {
    const count = props.nodes.length;
    return props.nodes.map((n, i) => {
        const angle = (2 * Math.PI * i) / Math.max(count, 1) - Math.PI / 2;
        const frac  = count === 0 ? 1 : 0.5 + 0.5 * (1 - n.total_tokens / maxTokens.value);
        const r     = R * (n.has_usage ? (0.55 + 0.45 * frac) : 1.0);
        return { ...n, x: CX + r * Math.cos(angle), y: CY + r * Math.sin(angle), radius: nodeRadius(n.total_tokens) };
    });
});

function selectNode(node) {
    selectedId.value = selectedId.value === node.id ? null : node.id;
    emit('select-agent', selectedId.value ? node : null);
}

function isTop(n) {
    return n.has_usage && n.total_tokens === maxTokens.value && maxTokens.value > 0;
}

function truncate(str, max = 9) {
    return str.length > max ? str.slice(0, max) + '…' : str;
}

function dashOffset(i) {
    return -(i * 4);
}
</script>

<template>
    <div class="overflow-hidden rounded-[14px]"
         style="background:var(--color-surface); border:1px solid var(--color-border-subtle); box-shadow:var(--shadow-soft)">

        <!-- Warm-tinted grid background matching 9Router -->
        <div class="relative overflow-hidden" style="background:var(--color-bg)">
            <svg class="absolute inset-0 h-full w-full" aria-hidden="true">
                <defs>
                    <pattern id="r9-grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(229,106,74,0.07)" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#r9-grid)" />
            </svg>

            <svg
                :viewBox="`0 0 ${WIDTH} ${HEIGHT}`"
                class="relative w-full"
                :style="{ maxHeight: '380px' }"
                aria-label="Agent topology graph"
            >
                <!-- Animated flow lines -->
                <g v-for="(n, i) in positioned" :key="'line-' + n.id">
                    <line
                        :x1="CX" :y1="CY" :x2="n.x" :y2="n.y"
                        :stroke="isTop(n) ? '#e56a4a' : '#e5e7eb'"
                        stroke-width="1.5"
                        :opacity="n.has_usage ? 0.7 : 0.3"
                    />
                    <!-- Animated dashes for active nodes -->
                    <line
                        v-if="n.has_usage"
                        :x1="CX" :y1="CY" :x2="n.x" :y2="n.y"
                        :stroke="isTop(n) ? '#e56a4a' : '#d1d5db'"
                        stroke-width="1.5"
                        stroke-dasharray="5 8"
                        :stroke-dashoffset="dashOffset(i)"
                        opacity="0.5"
                        class="flow-line-animated"
                    />
                </g>

                <!-- Agent nodes — white chip style -->
                <g
                    v-for="n in positioned"
                    :key="'node-' + n.id"
                    style="cursor: pointer"
                    role="button"
                    :aria-label="n.name"
                    @click="selectNode(n)"
                >
                    <!-- Pulse ring for top node -->
                    <circle
                        v-if="isTop(n)"
                        :cx="n.x" :cy="n.y" :r="n.radius + 8"
                        fill="none" stroke="#e56a4a" stroke-width="1.5" opacity="0.35"
                        class="node-pulse"
                    />
                    <!-- Solid orange ring for top -->
                    <circle
                        v-if="isTop(n)"
                        :cx="n.x" :cy="n.y" :r="n.radius + 5"
                        fill="none" stroke="#e56a4a" stroke-width="1.5" opacity="0.7"
                    />
                    <!-- Selected ring -->
                    <circle
                        v-if="selectedId === n.id"
                        :cx="n.x" :cy="n.y" :r="n.radius + 8"
                        fill="none" stroke="#3b82f6" stroke-width="1.5" opacity="0.5"
                    />
                    <!-- White chip fill — rounded rect for pill effect -->
                    <rect
                        :x="n.x - n.radius" :y="n.y - n.radius * 0.65"
                        :width="n.radius * 2" :height="n.radius * 1.3"
                        :rx="n.radius * 0.35"
                        fill="white"
                        :stroke="isTop(n) ? '#e56a4a' : (n.has_usage ? '#e5e7eb' : '#f3f4f6')"
                        stroke-width="1.5"
                        :opacity="n.has_usage ? 1 : 0.45"
                        style="filter: drop-shadow(0 1px 3px rgba(0,0,0,0.07))"
                    />
                    <!-- Node label -->
                    <text
                        :x="n.x" :y="n.y + 4"
                        text-anchor="middle"
                        font-size="8"
                        font-family="Inter, system-ui, sans-serif"
                        font-weight="500"
                        :fill="isTop(n) ? '#e56a4a' : (n.has_usage ? '#374151' : '#9ca3af')"
                        style="pointer-events: none; user-select: none"
                    >{{ truncate(n.name) }}</text>
                </g>

                <!-- Hub node — branded orange -->
                <circle
                    :cx="CX" :cy="CY" r="36"
                    fill="white" stroke="#e56a4a" stroke-width="2"
                    style="filter: drop-shadow(0 2px 10px rgba(229,106,74,0.22))"
                />
                <text :x="CX" :y="CY - 5" text-anchor="middle" font-size="8" font-family="Inter, system-ui, sans-serif" fill="#e56a4a" font-weight="700">Token</text>
                <text :x="CX" :y="CY + 8" text-anchor="middle" font-size="7" font-family="Inter, system-ui, sans-serif" fill="#e56a4a" font-weight="500">Mgmt</text>
            </svg>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap gap-4 px-4 py-2.5 text-[11px]"
             style="border-top:1px solid var(--color-border-subtle); color:var(--color-text-subtle)">
            <span class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-sm border bg-white" style="border-color:#e5e7eb"></span> Agent
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-sm border bg-white" style="border-color:#e56a4a"></span> Highest usage
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-sm" style="background:var(--color-surface-2)"></span> No usage
            </span>
        </div>
    </div>

    <p v-if="nodes.length === 0" class="py-6 text-center text-[13px]" style="color:var(--color-text-muted)">No agents found</p>
</template>
