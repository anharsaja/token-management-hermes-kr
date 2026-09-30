<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    nodes: { type: Array, default: () => [] },
});

const emit = defineEmits(['select-agent']);

const selectedId = ref(null);

const WIDTH  = 520;
const HEIGHT = 350;
const CX     = WIDTH  / 2;
const CY     = HEIGHT / 2;
const R      = 140;

const maxTokens = computed(() => Math.max(1, ...props.nodes.map(n => n.total_tokens)));

function nodeRadius(tokens) {
    const min = 18, max = 40;
    if (tokens === 0) return min;
    return min + ((tokens / maxTokens.value) * (max - min));
}

const positioned = computed(() => {
    const count = props.nodes.length;
    return props.nodes.map((n, i) => {
        const angle = (2 * Math.PI * i) / Math.max(count, 1) - Math.PI / 2;
        const frac  = count === 0 ? 1 : 0.5 + 0.5 * (1 - n.total_tokens / maxTokens.value);
        const r     = R * (n.has_usage ? (0.6 + 0.4 * frac) : 1.05);
        return {
            ...n,
            x:      CX + r * Math.cos(angle),
            y:      CY + r * Math.sin(angle),
            radius: nodeRadius(n.total_tokens),
        };
    });
});

function selectNode(node) {
    selectedId.value = selectedId.value === node.id ? null : node.id;
    emit('select-agent', selectedId.value ? node : null);
}

// UI.md colors
function nodeColor(n) {
    if (!n.has_usage) return '#1E293B';  // bg-elevated, very dim
    if (!n.is_active) return '#334155';  // text-disabled
    return '#818CF8';                    // accent-indigo (active)
}

function nodeBorderColor(n) {
    if (!n.has_usage) return '#334155';
    if (!n.is_active) return '#475569';
    return '#818CF8';
}

function isTop(n) {
    return n.has_usage && n.total_tokens === maxTokens.value && maxTokens.value > 0;
}

function truncate(str, max = 9) {
    return str.length > max ? str.slice(0, max) + '…' : str;
}
</script>

<template>
    <div
        class="rounded-[14px] border border-white/[0.07] p-5 backdrop-blur-[12px]"
        style="background: rgba(17,24,39,0.60)"
    >
        <p class="mb-3 text-[11px] font-medium uppercase tracking-[0.08em] text-[#94A3B8]">Agent Topology</p>

        <div class="overflow-x-auto">
            <svg
                :viewBox="`0 0 ${WIDTH} ${HEIGHT}`"
                class="w-full"
                :style="{ maxHeight: '350px' }"
                aria-label="Agent topology graph"
            >
                <!-- Connection lines: hub → nodes -->
                <line
                    v-for="n in positioned"
                    :key="'line-' + n.id"
                    :x1="CX" :y1="CY"
                    :x2="n.x" :y2="n.y"
                    stroke="rgba(255,255,255,0.07)"
                    stroke-width="1"
                    :opacity="n.has_usage ? 1 : 0.4"
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
                    <!-- Top agent glow ring -->
                    <circle
                        v-if="isTop(n)"
                        :cx="n.x" :cy="n.y"
                        :r="n.radius + 7"
                        fill="none"
                        stroke="#F97316"
                        stroke-width="1.5"
                        style="filter: drop-shadow(0 0 8px rgba(249,115,22,0.5))"
                    />
                    <!-- Selected ring -->
                    <circle
                        v-if="selectedId === n.id"
                        :cx="n.x" :cy="n.y"
                        :r="n.radius + 10"
                        fill="none"
                        stroke="#818CF8"
                        stroke-width="1.5"
                        opacity="0.7"
                    />
                    <!-- Node fill -->
                    <circle
                        :cx="n.x" :cy="n.y"
                        :r="n.radius"
                        :fill="nodeColor(n)"
                        :stroke="nodeBorderColor(n)"
                        stroke-width="1.5"
                        :opacity="n.has_usage ? 1 : 0.35"
                    />
                    <!-- Node label -->
                    <text
                        :x="n.x" :y="n.y + 4"
                        text-anchor="middle"
                        font-size="9"
                        font-family="Inter, system-ui, sans-serif"
                        :fill="n.has_usage ? '#F1F5F9' : '#475569'"
                        style="pointer-events: none; user-select: none"
                    >{{ truncate(n.name) }}</text>
                </g>

                <!-- Hub node -->
                <circle
                    :cx="CX" :cy="CY"
                    r="32"
                    fill="#111827"
                    stroke="#818CF8"
                    stroke-width="1.5"
                    style="filter: drop-shadow(0 0 12px rgba(129,140,248,0.30))"
                />
                <text :x="CX" :y="CY - 5"  text-anchor="middle" font-size="8" font-family="Inter, system-ui, sans-serif" fill="#818CF8" font-weight="600">Token</text>
                <text :x="CX" :y="CY + 7" text-anchor="middle" font-size="8" font-family="Inter, system-ui, sans-serif" fill="#818CF8" font-weight="600">Mgmt</text>
            </svg>
        </div>

        <!-- Empty state -->
        <p v-if="nodes.length === 0" class="mt-2 text-center text-[13px] text-[#475569]">No agents found</p>

        <!-- Legend -->
        <div class="mt-3 flex flex-wrap gap-4 text-[11px] text-[#475569]">
            <span class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full" style="background:#818CF8"></span> Active
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full" style="background:#334155"></span> No usage
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full border border-[#F97316]" style="background:transparent"></span> Highest
            </span>
        </div>
    </div>
</template>
