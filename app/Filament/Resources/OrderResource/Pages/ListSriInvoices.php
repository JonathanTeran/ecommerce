<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListSriInvoices extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Facturación Electrónica';

    protected static ?string $navigationGroup = 'Facturación';

    protected static ?string $title = 'Facturación Electrónica';

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNotNull('sri_access_key'))
            ->defaultSort('created_at', 'desc');
    }
}
