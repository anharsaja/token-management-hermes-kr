<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    nodes: { type: Array, default: () => [] },
});

const emit = defineEmits(['select-agent']);

const selectedId = ref(null);

const WIDTH  = 500;
const HEIGHT = 340;
const CX     = WIDTH  / 2;
const CY     = HEIGHT / 2;
const R      = 130; // orbit radius

const maxTokens = computed(() => Math.max(1, ...props.nodes.map(n => n.total_tokens)));

function nodeRadius(tokens) {
    const min = 18, max = 38;
    if (tokens === 0) return min;
    return min + ((tokens / maxTokens.value) * (max - min));
}

const positioned = computed(() => {
    const count = props.nodes.length;
    return props.nodes.map((n, i) => {
        const angle = (2 * Math.PI * i) / Math.max(count, 1) - Math.PI / 2;
        // closer to center = more tokens
        const frac = count === 0 ? 1 : 0.5 + 0.5 * (1 - n.total_tokens / maxTokens.value);
        const r = R * (n.has_usage ? (0.6 + 0.4 * frac) : 1.05);
        return {
            ...n,
            x: CX + r * Math.cos(angle),
            y: CY + r * Math.sin(angle),
            radius: nodeRadius(n.total_tokens),
        };
    });
});

function selectNode(node) {
    selectedId.value = selectedId.value === node.id ? null : node.id;
    emit('select-agent', selectedId.value ? node : null);
}

function nodeColor(n) {
    if (!n.has_usage)  return '#4b5563'; // gray
    if (!n.is_active)  return '#6b7280';
    return '#6366f1';                     // indigo
}

function isTop(n) {
    return n.has_usage && n.total_tokens === maxTokens.value && maxTokens.value > 0;
}

function truncate(str, max = 10) {
    return str.length > max ? str.slice(0, max) + '…' : str;
}
</script>

<template>
    <div class="rounded-xl border border-gray-700 bg-gray-800/60 p-4 backdrop-blur">
        <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-gray-400">Agent Topology</p>
        <div class="overflow-x-auto">
            <svg :viewBox="`0 0 ${WIDTH} ${HEIGHT}`" class="w-full" :style="{ maxHeight: '340px' }" aria-label="Agent topology graph">
                <!-- Lines hub → nodes -->
                <line
                    v-for="n in positioned"
                    :key="'line-' + n.id"
                    :x1="CX" :y1="CY"
                    :x2="n.x" :y2="n.y"
                    stroke="#374151"
                    stroke-width="1"
                    :opacity="n.has_usage ? 0.6 : 0.2"
                />

                <!-- Agent nodes -->
                <g
                    v-for="n in positioned"
                    :key="'node-' + n.id"
                    style="cursor: pointer"
                    role="button"
                    :aria-label="n.name"
                    @click="selectNode(n)"
                >
                    <!-- Highlight ring for top agent -->
                    <circle
                        v-if="isTop(n)"
                        :cx="n.x" :cy="n.y"
                        :r="n.radius + 5"
                        fill="none"
                        stroke="#f97316"
                        stroke-width="2"
                        opacity="0.8"
                    />
                    <!-- Selected ring -->
                    <circle
                        v-if="selectedId === n.id"
                        :cx="n.x" :cy="n.y"
                        :r="n.radius + 7"
                        fill="none"
                        stroke="#a5b4fc"
                        stroke-width="2"
                        opacity="0.9"
                    />
                    <circle
                        :cx="n.x" :cy="n.y"
                        :r="n.radius"
                        :fill="nodeColor(n)"
                        :opacity="n.has_usage ? 1 : 0.35"
                    />
                    <text
                        :x="n.x" :y="n.y + 4"
                        text-anchor="middle"
                        font-size="9"
                        fill="white"
                        :opacity="n.has_usage ? 1 : 0.5"
                        style="pointer-events: none; user-select: none"
                    >{{ truncate(n.name) }}</text>
                </g>

                <!-- Hub node -->
                <circle :cx="CX" :cy="CY" r="30" fill="#1e293b" stroke="#6366f1" stroke-width="2" />
                <text :x="CX" :y="CY - 4" text-anchor="middle" font-size="9" fill="#a5b4fc" font-weight="bold">Token</text>
                <text :x="CX" :y="CY + 8" text-anchor="middle" font-size="9" fill="#a5b4fc" font-weight="bold">Mgmt</text>
            </svg>
        </div>

        <!-- No agents -->
        <p v-if="nodes.length === 0" class="mt-2 text-center text-xs text-gray-500">No agents found</p>

        <!-- Legend -->
        <div class="mt-2 flex flex-wrap gap-3 text-xs text-gray-400">
            <span class="flex items-center gap-1"><span class="inline-block h-2 w-2 rounded-full bg-indigo-500"></span> Active</span>
            <span class="flex items-center gap-1"><span class="inline-block h-2 w-2 rounded-full bg-gray-500"></span> No usage</span>
            <span class="flex items-center gap-1"><span class="inline-block h-2 w-2 rounded-full border border-orange-500 bg-transparent"></span> Highest</span>
        </div>
    </div>
</template>
