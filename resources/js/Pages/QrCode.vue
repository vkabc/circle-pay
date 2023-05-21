<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import {Head} from '@inertiajs/vue3';
import GuestLayout from "@/Layouts/GuestLayout.vue";

import {Link, useForm} from '@inertiajs/vue3';

const inertiaForm = useForm({
    id: '',
});


import {onMounted, ref} from 'vue'
import axios from 'axios'

import {v4 as uuidv4} from 'uuid'


import openPGP from './openpgp'

defineProps<{
    msg: string,
    id: number,
    svg: string,
    url: string,
}>()

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

const chargeCard = async () => {
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
        const cardDetails = {cvv: form.value.cvv}


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

const makeCreateCardCall = async () => {

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
        const {encryptedMessage, keyId} = encryptedData

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
    <Head title="Dashboard"/>

    <GuestLayout>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-col justify-center  bg-white  shadow-sm sm:rounded-lg">


                    <div class="mb-4 inline-flex max-h-full w-full max-w-full justify-center">
                        <div v-html="svg"></div>

                    </div>


                    <a :href="url" class="inline-flex justify-center hover:underline text-gray-500">
                        {{ url }}
                    </a>


                </div>
            </div>
        </div>
    </GuestLayout>
</template>
