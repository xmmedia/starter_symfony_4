<template>
    <div class="form-wrap my-8 pb-10">
        <div class="mb-8 py-6 bg-gray-900 text-center text-white">
            <h1 class="my-0 text-[1.75rem] leading-none font-semibold">
                <slot name="heading"></slot>
            </h1>
        </div>
        <div class="px-8">
            <slot />
        </div>
    </div>
</template>
