<?php

namespace App\Livewire;

use App\Models\Agent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Footer;
use PowerComponents\LivewirePowerGrid\Header;
use PowerComponents\LivewirePowerGrid\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class AgentTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'agents';

    public array $name;

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            Exportable::make('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            Header::make()->showSearchInput(),
            Footer::make()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function header(): array
    {
        return [
            Button::add('bulk-delete')
                ->slot(__('Hapus agen (<span x-text="window.pgBulkActions.count(\'' . $this->tableName . '\')"></span>)'))
                ->class('btn btn-danger fw-semibold')
                ->dispatch('bulkDelete.' . $this->tableName, []),
        ];
    }

    public function datasource(): Builder
    {
        return Agent::query()->with('branch');
    }

    public function relationSearch(): array
    {
        return [
            'branch' => [ 
                'name', // column enabled to search
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
        ->add('id')
        // SHOW RELATIONSHIP DATA 
        ->add('branch_id', function ($agent) {
            return e($agent->branch->name);
        })
        ->add('name')
        ->add('status')
        ->add('account')
        ->add('created_at')
        ->add('created_at_formatted', function ($agent) {
            return e(Carbon::parse($agent->created_at)->translatedFormat('l, d F Y'));
        });
    }

    public function columns(): array
    {
        return [
            Column::make('Kode', 'id')
                ->sortable()
                ->searchable(),

            Column::make('Kantor Cabang', 'branch_id')
                ->sortable()
                ->searchable(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Status', 'status')
                ->sortable()
                ->searchable(),

            Column::make('Rekening', 'account')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Created At', 'created_at')
                ->sortable()
                ->searchable(),

            Column::make('Updated At', 'updated_at')
                ->sortable()
                ->searchable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function rules()
    {
        return [
            'name.*' => ['required', 'string', 'max:255'],
        ];
    }

    public function onUpdatedEditable(string|int $id, string $field, string $value): void
    {
        $this->validate();

        Agent::query()->find($id)->update([
            $field => e($value),
        ]);
    }

    #[\Livewire\Attributes\On('bulkDelete.{tableName}')]
    public function bulkDelete(): void
    {
        $this->js('alert(window.pgBulkActions.get(\'' . $this->tableName . '\'))');
        if ($this->checkboxValues)
        {
            Agent::destroy($this->checkboxValues);
            $this->js('window.pgBulkActions.clearAll()'); // clear the count on the interface.
        }
    }

    #[\Livewire\Attributes\On('pg:eventRefresh-AgentTable')]
    public function importedData(): void
    {
        $this->fillData();
    }

    public function actions(Agent $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('btn btn-outline-primary')
                ->dispatch('open-edit-agent-modal', ['agentID' => $row->id, 'branchID' => $row->branch_id, 'agentName' => $row->name, 'agentStatus' => $row->status, 'agentAccount' => $row->account]),
        ];
    }
}
