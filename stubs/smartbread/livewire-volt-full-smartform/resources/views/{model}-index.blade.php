<x-layouts.app>
    
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ _('{Model}') }}
        </h2>
    </x-slot>

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show  w-75" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card w-75">
        <div class="card-body">
            <h3 class="card-title">
            <div class="row">

                <!-- Form Name -->
                <div class="col-auto mr-auto">
                    {{ _('{Model} Management') }}
                </div>
                <!-- / Form Name -->

                <div class="col-auto">
                    <!-- Add Link -->
                    <a href="{{ route('{module}::{model}.create') }}" class="btn btn-sm btn-outline-primary" title="{{ _('Add {Model}')}}">
                        <i class="bi bi-plus-circle"></i>
                    </a>
                    <!-- / Add Link -->
                </div>
            </h3>


            <div class="card-body p-4">

                @if (empty(${model}_pagination))
                    <!-- No data -->
                    <div class="alert alert-info text-center">
                        {{ _("No {Model} found.") }}
                    </div>
                    <!-- / No data -->
                @else

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <!-- Table Header Fields -->
                                    <th style="text-align: justify; width: 150px;"> {{ _('{model}-field-id') }}
                                    </th>
                                    <th style="text-align: justify; width: 150px;"> {{ _('{model}-field-name') }} </th>
                                    <th class="text-center" style="white-space: nowrap; width: 50px; min-width: 50px; max-width: 50px">{{ _('Actions') }}</th>
                                    <!-- / Table Header Fields -->
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(${model}_pagination as ${model})
                                    <tr>
                                        <!-- Table Data -->

                                        <td>{{ ${model}->id }}
                                        <td>{{ ${model}->name }}

                                        <!-- / Table Data -->

                                        <td class="text-center" style="white-space: nowrap; width: 50px; min-width: 50px; max-width: 50px;">
                                            <div class="d-flex justify-content-center gap-1">

                                                <!-- Edit and Delete Actions -->

                                                <a href="{{ route('{module}::{model}.edit', ${model}->id) }}" 
                                                    class="btn btn-sm btn-outline-primary" 
                                                    title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                </a>

                                                <form action="{{ route('{module}::{model}.destroy', ${model}->id) }}" 
                                                        method="POST" 
                                                        onsubmit="return confirm(_('Are you sure you want to remove this item?'))">
                                                        @csrf
                                                        @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Excluir">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
        
                                                <!-- Edit and Delete Actions -->
                                            </div>
                                        </td>

                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                        <!-- card-footer -->
                        <div class="card-footer text-muted">
                            <div class="mt-2">

                                <!-- Pagination -->
                                @if (! empty(${model}_pagination))
                                    {{ ${model}_pagination->links() }}
                                @endif
                                <!-- /Pagination -->

                            </div>
                        </div>
                    @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

</x-layouts.app>