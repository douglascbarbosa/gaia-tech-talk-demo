<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteDeveloper from '@/components/DeleteDeveloper.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const developer = computed(
    () => page.props.auth.developer ?? page.props.auth.user!,
);
</script>

<template>
    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Profile information"
            description="Update your name, email, availability, GitHub, and address"
        />

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    class="mt-1 block w-full"
                    name="name"
                    :default-value="developer.name"
                    required
                    autocomplete="name"
                    placeholder="Full name"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    name="email"
                    :default-value="developer.email"
                    required
                    autocomplete="username"
                    placeholder="Email address"
                />
                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="github_profile">GitHub profile</Label>
                <Input
                    id="github_profile"
                    class="mt-1 block w-full"
                    name="github_profile"
                    :default-value="developer.github_profile ?? ''"
                    autocomplete="off"
                    placeholder="https://github.com/you or @handle"
                />
                <InputError class="mt-2" :message="errors.github_profile" />
            </div>

            <div class="grid gap-2">
                <Label for="status">Availability</Label>
                <select
                    id="status"
                    name="status"
                    required
                    class="border-input bg-background ring-offset-background focus-visible:ring-ring flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] focus-visible:ring-[3px] focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option
                        value="working"
                        :selected="developer.status === 'working'"
                    >
                        Working
                    </option>
                    <option
                        value="open_for_new_jobs"
                        :selected="developer.status === 'open_for_new_jobs'"
                    >
                        Open for new jobs
                    </option>
                </select>
                <InputError class="mt-2" :message="errors.status" />
            </div>

            <Heading
                variant="small"
                title="Address"
                description="Leave all address fields empty, or fill in every field."
                class="pt-2"
            />

            <div class="grid gap-2">
                <Label for="address_street">Street</Label>
                <Input
                    id="address_street"
                    class="mt-1 block w-full"
                    name="address_street"
                    :default-value="developer.address_street ?? ''"
                    autocomplete="street-address"
                />
                <InputError class="mt-2" :message="errors.address_street" />
            </div>

            <div class="grid gap-2">
                <Label for="address_city">City</Label>
                <Input
                    id="address_city"
                    class="mt-1 block w-full"
                    name="address_city"
                    :default-value="developer.address_city ?? ''"
                    autocomplete="address-level2"
                />
                <InputError class="mt-2" :message="errors.address_city" />
            </div>

            <div class="grid gap-2">
                <Label for="address_postal_code">Postal code</Label>
                <Input
                    id="address_postal_code"
                    class="mt-1 block w-full"
                    name="address_postal_code"
                    :default-value="developer.address_postal_code ?? ''"
                    autocomplete="postal-code"
                />
                <InputError class="mt-2" :message="errors.address_postal_code" />
            </div>

            <div class="grid gap-2">
                <Label for="address_country">Country</Label>
                <Input
                    id="address_country"
                    class="mt-1 block w-full"
                    name="address_country"
                    :default-value="developer.address_country ?? ''"
                    autocomplete="country-name"
                />
                <InputError class="mt-2" :message="errors.address_country" />
            </div>

            <div v-if="mustVerifyEmail && !developer.email_verified_at">
                <p class="-mt-4 text-sm text-muted-foreground">
                    Your email address is unverified.
                    <Link
                        :href="send()"
                        as="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                    >
                        Click here to resend the verification email.
                    </Link>
                </p>

                <div
                    v-if="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-profile-button"
                    >Save</Button
                >
            </div>
        </Form>
    </div>

    <DeleteDeveloper />
</template>
