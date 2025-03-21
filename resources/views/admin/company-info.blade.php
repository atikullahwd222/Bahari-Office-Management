<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Company Info') }}
        </h2>
    </x-slot>

    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <div class="max-w-xl">
            @include('admin.partials.companyinfo')
        </div>
    </div>
</x-app-layout>
