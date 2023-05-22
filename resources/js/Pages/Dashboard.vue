<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from '@inertiajs/vue3';
import {onBeforeMount} from "vue";

interface Record {
    id: number;
    from: string;
    to: string;
    amount: number;
}


const props = defineProps<{

    names: string[],
    amount: number,
    id: number,
    records: Record,
}>()

const goMerchantLink = () => {
    window.location.href = route('qrcode', {id: props.id})
}

onBeforeMount(() => {
    console.log(props.records)
})
</script>

<template>
    <Head title="Dashboard"/>

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">

                <h2 class="font-medium text-xl text-gray-800 leading-tight">Credit Available: ${{ amount }}</h2>
                <button type="button" @click="goMerchantLink"
                        class="ml-4 text-gray-900 bg-white hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:focus:ring-gray-600 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:bg-gray-700 mr-2 mb-2">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="w-6 h-6 mr-1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
                    </svg>
                    Get Merchant Code/Link
                </button>


                <h1 class="ml-auto text-3xl font-bold text-gray-400">{{ $page.props.auth.user.name }}</h1>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">


                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    Type
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    To
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Amount
                                </th>

                                <th scope="col" class="px-6 py-3">
                                    Done on
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(record, idx) in records"
                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">

                                <th scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ record.to === id ? 'Received' : 'Sent' }}
                                </th>
                                <td class="px-6 py-4">
                                    {{ record.to === id ? "N/A" : names[idx] }}
                                </td>
                                <td :class="record.to === id?'text-green-600':'text-red-700'"
                                    class="px-6 py-4 font-semibold">
                                    {{ record.amount }}
                                </td>

                                <td class="px-6 py-4">

                                    {{ record.created_at }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
