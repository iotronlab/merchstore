<?php

namespace App\Filament\Resources\Product\ProductResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AllStocksRelationManager extends RelationManager
{
    protected static string $relationship = 'stocks';

    protected static ?string $recordTitleAttribute = 'in_stock_quantity';
    protected static ?string $label = 'Stock';

//    public static function canViewForRecord(Model $ownerRecord): bool
//    {
//        return $ownerRecord->type === Product::SIMPLE;
//    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([


                TextInput::make('init_quantity')
                    ->numeric()
                    ->label(__('Quantity'))
                    ->minValue(function (?Model $record) {
                        if ($record) {
                            return max(0, $record->sold_quantity);
                        }
                        return 0;
                    }
                )->required(),


            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('init_quantity'),
                TextColumn::make('sold_quantity')->label('Sold Quantity'),
                TextColumn::make('in_stock_quantity')->label('In Stock Quantity'),
                Tables\Columns\IconColumn::make('in_stock')->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle'),
                TextColumn::make('updated_at')->dateTime()->toggleable(),
                TextColumn::make('created_at')->dateTime()->toggleable()->toggledHiddenByDefault(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->createAnother(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
