<template>
    <div class="form-wrap my-8 pb-10">
        <div class="form_header-wrap">
            <h1 class="form-header"><slot name="heading"></slot></h1>
        </div>
        <div class="px-8">
            <slot />
        </div>
    </div>
</template>
