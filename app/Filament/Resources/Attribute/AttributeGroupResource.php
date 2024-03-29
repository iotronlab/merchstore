<?php

namespace App\Filament\Resources\Attribute;

use App\Filament\Resources\Attribute\AttributeGroupResource\Pages;
use App\Filament\Resources\Attribute\AttributeGroupResource\RelationManagers;
use App\Models\Filter\FilterGroup;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttributeGroupResource extends Resource
{
    protected static ?string $model = FilterGroup::class;
    protected static ?string $navigationGroup = 'Filters';
    protected static ?string $slug = 'attribute-groups';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(__('Information'))->schema([
                    TextInput::make('admin_name')
                        ->label('Name')
                        ->placeholder(__('Enter Group Name'))
                        ->required()
                        ->columnSpan(2),
                    TextInput::make('code')
                        ->label(__('Code'))
                        ->placeholder(__('Enter Group Code'))
                        ->required(),
                    TextInput::make('position')
                        ->placeholder(__('Enter Position'))
//                        ->mask(
////                            fn (TextInput\Mask $mask) => $mask
////                                ->numeric()
////                                ->decimalPlaces(0)
////                                ->decimalSeparator('.')
////                                ->minValue(1)
////                                ->maxValue(999999)
////                                ->thousandsSeparator(',')
//                        )
                        ->required()
                        ->label(__('Position')),

                    Radio::make('type')
                        ->required()
                        ->label(__('Choose Type'))
                        ->columnSpan(2)
                        ->nullable()
                        ->options(FilterGroup::TYPE_OPTIONS)->inline(),

                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('admin_name')->label('Name'),
                TextColumn::make('code')->label('Code'),


                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'secondary',
                        'primary' => FilterGroup::FILTERABLE,
                        'success' => FilterGroup::STATIC,

                    ]),


                TextColumn::make('position')->label('Position'),
                TextColumn::make('updated_at')->label('Modified On')->since()->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttributeGroups::route('/'),
            'create' => Pages\CreateAttributeGroup::route('/create'),
            'view' => Pages\ViewAttributeGroup::route('/{record}'),
            'edit' => Pages\EditAttributeGroup::route('/{record}/edit'),
        ];
    }
}
