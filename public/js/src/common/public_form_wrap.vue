<template>
    <div class="form-wrap my-8 pb-10">
        <div class="mb-8 py-6 text-white text-center bg-gray-900">
            <h1 class="text-[28px] my-0 font-semibold leading-none font-headings">
                <slot name="heading"></slot>
            </h1>
        </div>
        <div class="px-8">
            <slot />
        </div>
    </div>
</template>
