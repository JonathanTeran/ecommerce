<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Configuración Global';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Plantilla de Email';

    protected static ?string $pluralModelLabel = 'Plantillas de Email';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informacion de la Plantilla')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                                if ($state) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('category')
                            ->label('Categoria')
                            ->options([
                                'transactional' => 'Transaccional',
                                'marketing' => 'Marketing',
                                'system' => 'Sistema',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Contenido del Email')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Asunto')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Puedes usar variables como {customer_name}, {order_number}, etc.'),
                        Forms\Components\TagsInput::make('variables')
                            ->label('Variables Disponibles')
                            ->helperText('Lista de variables disponibles para esta plantilla (ej: {customer_name})')
                            ->placeholder('Agregar variable'),
                        Forms\Components\RichEditor::make('body_html')
                            ->label('Cuerpo del Email')
                            ->required()
                            ->toolbarButtons([
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoria')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'transactional' => 'primary',
                        'marketing' => 'success',
                        'system' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'transactional' => 'Transaccional',
                        'marketing' => 'Marketing',
                        'system' => 'Sistema',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Asunto')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoria')
                    ->options([
                        'transactional' => 'Transaccional',
                        'marketing' => 'Marketing',
                        'system' => 'Sistema',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailTemplates::route('/'),
            'create' => Pages\CreateEmailTemplate::route('/create'),
            'edit' => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
