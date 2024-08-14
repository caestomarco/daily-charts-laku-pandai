<?php

namespace App\Livewire;

use App\Models\Branch;
use Illuminate\Support\Carbon;
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

final class BranchTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'branches';

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
                ->slot(__('Hapus cabang (<span x-text="window.pgBulkActions.count(\'' . $this->tableName . '\')"></span>)'))
                ->class('btn btn-danger fw-semibold')
                ->dispatch('bulkDelete.' . $this->tableName, []),
        ];
    }

    public function datasource(): Builder
    {
        return Branch::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()->add('id')->add('name')->add('status')->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Kode', 'id')
                ->sortable()
                ->searchable(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable()
                ->editOnClick(),

            Column::make('Status', 'status')
                ->sortable()
                ->searchable(),

            Column::make('Created at', 'created_at')
                ->sortable()
                ->searchable(),

            Column::make('Updated at', 'updated_at')
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

        Branch::query()->find($id)->update([
            $field => e($value),
        ]);
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    #[\Livewire\Attributes\On('bulkDelete.{tableName}')]
    public function bulkDelete(): void
    {
        $this->js('alert(window.pgBulkActions.get(\'' . $this->tableName . '\'))');
        if($this->checkboxValues){
            Branch::destroy($this->checkboxValues);
            $this->js('window.pgBulkActions.clearAll()'); // clear the count on the interface.
        }
    }

    #[\Livewire\Attributes\On('pg:eventRefresh-BranchTable')]
    #[\Livewire\Attributes\On('hide-add-branch-modal')]
    public function onUpdate(): void
    {
        $this->fillData();
    }

    public function actions(Branch $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('btn btn-outline-primary')
                ->dispatch('edit', ['rowId' => $row->id])
        ];
    }
}
