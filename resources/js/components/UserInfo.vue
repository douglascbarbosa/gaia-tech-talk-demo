<script setup lang="ts">
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { Developer } from '@/types';

type Props = {
    developer: Developer;
    showEmail?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

// Compute whether we should show the avatar image
const showAvatar = computed(
    () => props.developer.avatar && props.developer.avatar !== '',
);
</script>

<template>
    <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
        <AvatarImage
            v-if="showAvatar"
            :src="developer.avatar!"
            :alt="developer.name"
        />
        <AvatarFallback class="rounded-lg text-black dark:text-white">
            {{ getInitials(developer.name) }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ developer.name }}</span>
        <span v-if="showEmail" class="truncate text-xs text-muted-foreground">{{
            developer.email
        }}</span>
    </div>
</template>
