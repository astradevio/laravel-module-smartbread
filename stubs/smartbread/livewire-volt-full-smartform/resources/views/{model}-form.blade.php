<x-layouts.app>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('{Model}') }}
        </h2>
    </x-slot>

    <div class="card-body p-4">
        <!-- Error -->
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> 
                {{ _('Please, correct the errors above: ')}}:
                <ul class="mt-2 mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <!-- /Error -->


        <div class="card w-75">
            <div class="card-header">
                <div class="col-auto mr-auto">
                    {{ _('{Model} Management') }}
                </div>
            </div>
            <div class="card-body">

            <!-- Form Header -->
            <form id="{model}_form" method="POST" 
                action="{{ isset(${model}) ? route('{module}::{model}.update', ${model}->id): route('{module}::{model}.store') }}"
                method="post">
                @csrf
                @if (isset(${model})) 
                    @method('PUT')
                @endif
            <!-- /Form Header -->

                <!-- Form inputs -->

                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="bi bi-hash me-1"></i>{{ _('{model}-field-name')}}</label>
                    <input type="text" class="form-control" id="name" name="name" 
                    @if (isset(${model})) 
                        value="{{ ${model}->name }}"
                    @else
                        value=""
                    @endif
                    >
                
                    @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- / Form inputs -->

                <!-- Reset and Submit Buttons -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('{module}::{model}.index') }}" class="btn btn-outline-secondary btn-custom">
                        <i class="bi bi-arrow-left me-1"></i> {{ _('Back') }}
                    </a>
                    <button type="submit" class="btn btn-success btn-custom">
                        <i class="bi bi-check-circle me-1"></i> 
                        {{ isset(${model}) ?  _('Update'): _('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-layouts.app>
