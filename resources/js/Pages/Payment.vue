<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import GuestLayout from "@/Layouts/GuestLayout.vue";



import {onMounted, ref} from 'vue'
import axios from 'axios'

import { v4 as uuidv4 } from 'uuid'


import openPGP from './openpgp'

defineProps<{ msg: string }>()

const count = ref(0)

const form = ref({
    amount: "",
    cardNumber: "",
    cvv: "",
    expiry: "",
})

const prefill = () => {

    form.value.amount = "100";
    form.value.cardNumber = "4007410000000006";
    form.value.cvv = "653";
    form.value.expiry = "2024-06";

};

const chargeCard = async () =>  {
    isLoading.value = true;
    try {
        const card = (await makeCreateCardCall()).data.data

        if (card && card.id) {

            const payment = await makeChargeCall(card.id)
            window.location.href = '#/sucess'
        }
    } catch (error) {
        // this.error = error
        // this.showError = true
    } finally {
        // this.loading = false
    }
}

export interface CreateCardPaymentPayload extends BasePaymentPayload {
    verification?: string
    autoCapture?: boolean
    verificationSuccessUrl?: string
    verificationFailureUrl?: string
    keyId?: string
    encryptedData?: string
}
interface BasePaymentPayload {
    idempotencyKey: string
    amount: {
        amount: string
        currency: string
    }
    source: {
        id: string
        type: string
    }
    description: string
    channel: string
    metadata: MetaData
}

const isLoading = ref(false)
const makeChargeCall = async (cardId: string) => {
    // this.loading = true

    const amountDetail = {
        amount: form.value.amount,
        currency: 'USD',
    }
    const sourceDetails = {
        id: cardId,
        type: 'card',
    }

    const payload: CreateCardPaymentPayload = {
        idempotencyKey: uuidv4(),
        amount: amountDetail,
        verification: 'cvv',
        source: sourceDetails,
        description: "Store payment",
        channel: "",
        metadata: {
            phoneNumber: "+12025550180",
            email: "customer-0003@circle.com",
            sessionId: 'xxx',
            ipAddress: '172.33.222.1',
        },
    }

    try {
        const cardDetails = { cvv: form.value.cvv }


        const publicKey = await getPCIPublicKey()
        const encryptedData = await openPGP.encrypt(cardDetails, publicKey.data.data as PublicKey)

        payload.encryptedData = encryptedData.encryptedMessage
        payload.keyId = encryptedData.keyId

        const payment = (await createPayment(payload)).data.data
        return payment;
    } catch (error) {
        // this.error = error
        // this.showError = true
    } finally {
        // this.loading = false
    }
}


function createPayment(payload: BasePaymentPayload) {
    const url = '/v1/payments'
    return instance.post(url, payload)
}

interface MetaData {
    email: string
    phoneNumber?: string
    sessionId: string
    ipAddress: string
}
interface CreateCardPayload {
    idempotencyKey: string
    keyId: string
    encryptedData: string
    billingDetails: {
        name: string
        city: string
        country: string
        line1: string
        line2: string
        district: string
        postalCode: string
    }
    expMonth: number
    expYear: number

    metadata: MetaData
}


function getPCIPublicKey() {
    const url = '/v1/encryption/public'

    return instance.get(url)
}

interface PublicKey {
    keyId: string
    publicKey: string
}

function createCard(payload: CreateCardPayload) {
    const url = '/v1/cards'
    return instance.post(url, payload)
}
const makeCreateCardCall = async() => {

//  this.loading = true

    const payload: CreateCardPayload = {
        idempotencyKey: uuidv4(),
        expMonth: parseInt("1"),
        expYear: parseInt("2025"),
        keyId: '',
        encryptedData: '',
        billingDetails: {
            line1: "Test",
            line2: "",
            city: "Test City",
            district: "MA",
            postalCode: "11111",
            country: "US",
            name: "Customer 003",
        },
        metadata: {
            phoneNumber: "+12025550180",
            email: "customer-0003@circle.com",
            sessionId: 'xxx',
            ipAddress: '172.33.222.1',
        },
    }


    try {
        const publicKey = await getPCIPublicKey()
        const cardDetails = {
            number: form.value.cardNumber.replace(/\s/g, ''),
            cvv: form.value.cvv,
        }

        const encryptedData = await openPGP.encrypt(cardDetails, publicKey.data.data as PublicKey)
        const { encryptedMessage, keyId } = encryptedData

        payload.keyId = keyId
        payload.encryptedData = encryptedMessage

        return await createCard(payload)
    } catch (error) {
        console.log(error)
        // this.error = error
        // this.showError = true
    } finally {
        // this.loading = false
    }
}
axios.defaults.headers.common['Authorization'] = import.meta.env.VITE_API_KEY;

onMounted(() => {

})
const instance = axios.create({
    baseURL: getAPIHostname(),
})
function getAPIHostname() {
    // If app is running on localhost (ie, in  dev) the URL is provided via an environment variable (.env file), use that.
    // Otherwise, base it off the window location.
    // if (window.location && window.location.hostname === 'localhost') {
    //   return import.meta.env.baseUrl
    return "https://api-sandbox.circle.com"
    // }
    // return window.location.origin.replace('sample', 'api')

}
</script>

<template>
    <Head title="Dashboard" />

    <GuestLayout>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    <div class="flex align-center">

                        <p class="tracking-widest text-gray-500 md:text-lg dark:text-gray-400 mb-6">Bedok Chicken Rice Payment</p>

                        <button @click="prefill" type="button"
                                class="ml-4 text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 font-sm rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            Prefill
                        </button>
                    </div>
                    <form class="text-left">
                        <div class="mb-6">
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Amount</label>
                            <input v-model="form.amount" type="text" id="text"
                                   class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"
                                   placeholder="$0.00" required>
                        </div>
                        <div class="mb-6">
                            <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Card Number</label>
                            <input v-model="form.cardNumber" type="text" id="password"
                                   class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"
                                   required>
                        </div>
                        <div class="mb-6">
                            <label for="repeat-password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">CVV</label>
                            <input v-model="form.cvv" type="text" id="repeat-password"
                                   class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"
                                   required>
                        </div>

                        <div class="mb-6">
                            <label for="repeat-password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Expiry</label>
                            <input v-model="form.expiry" type="month" id="repeat-password"
                                   class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"
                                   required>
                        </div>
                        <div class="flex items-start mb-6">
                            <div class="flex items-center h-5">
                                <input id="terms" type="checkbox" value=""
                                       class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800"
                                       required>
                            </div>
                            <label for="terms" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">I agree with the <a href="#"
                                                                                                                                     class="text-blue-600 hover:underline dark:text-blue-500">terms
                                and conditions</a></label>
                        </div>
                        <button type="button" @click="chargeCard"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            <div class="flex">

                                {{ isLoading?"Paying ...  ":"Pay With Credit Card"}}

                                <svg v-show="isLoading" aria-hidden="true" class="ml-2 w-5 h-5 mr-2 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                            </div>
                        </button>
                    </form>


                </div>
            </div>
        </div>
    </GuestLayout>
</template>
