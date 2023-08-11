<?php

namespace App\Filament\Resources\Promotion\VoucherResource\Pages;

use App\Filament\Resources\Promotion\VoucherResource;
use App\Helpers\Money\Money;
use App\Helpers\Promotion\Voucher\VoucherHelper;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVoucher extends CreateRecord
{
    protected static string $resource = VoucherResource::class;
    private VoucherHelper $voucherHelper;
    public $conditions;

    public function mount(): void
    {

        $this->voucherHelper = new VoucherHelper();
        $this->conditions = $this->voucherHelper->getCondition();
        $this->fillForm();

       // parent::mount(); // TODO: Change the autogenerated stub
    }









    protected function getFormSchema(): array
    {
        return [

            Fieldset::make('General Information')
                ->schema([

                    TextInput::make('name')
                        ->placeholder(__('Enter Voucher Name'))
                        ->maxLength(250)
                        ->hint(__('Max: 250'))
                        ->columnSpan(2)
                        ->required(),


                    Select::make('customerGroup')
                        ->multiple()
                        ->relationship('customer_groups', 'name')
                        ->required(),

                    Textarea::make('description')
                        ->placeholder('Write Briefly About This Voucher')
                        ->hint(__('Max: 30,000'))
                        ->maxLength(30000)
                        ->columnSpanFull(),

                    Toggle::make('status')->inline(true),

                    TextInput::make('sort_order')
                        ->label('Priority')
                        ->placeholder('Set Priority')
                        ->numeric()
                        ->default(0)
                        ->inlineLabel()
                        ->required(),

                ])->columns(3),





            Fieldset::make('Voucher Timeline & Usage')
                ->schema([
                    DateTimePicker::make('starts_from')->required()->placeholder('Set Start Date And Time'),
                    DateTimePicker::make('ends_till')->required()->placeholder('Set End Date And Time'),
                    TextInput::make('usage_per_customer')->label('Usage Per Customer')->required(),
                    TextInput::make('coupon_usage_limit')->label('Coupon Usage Limit')->required(),
                ])->columns(2),



            Fieldset::make('Discount Information')
                ->schema([
                    TextInput::make('discount_amount')
                        ->label('Discount Amount')
                        ->mask(
                            fn (TextInput\Mask $mask) => $mask->numeric()
                                ->decimalPlaces(2)
                                ->decimalSeparator('.')
                                ->minValue(1)
                                ->maxValue(99999999)
                                ->thousandsSeparator(',')
                        )
                        ->required()
                        ->placeholder('Enter Discount')
                        ->hint(__('eg: 45020 = '.Money::format(45020)))
                        ->afterStateUpdated(function (\Closure $set, $state){
                            $set('formatted_discount',Money::format($state));
                        })
                        ->lazy(),

                    TextInput::make('formatted_discount')->label(__('Discount (Formatted)'))->disabled(),


                    TextInput::make('discount_quantity')->label('Max Allowed Discountable Quantity'),
                    TextInput::make('discount_step')->label('By X Quantity'),
                ])->columns(2),




            Fieldset::make('Action Information')
                ->schema([
                    Select::make('action_type')->options([
                        'by_percent' => 'Percentage of Product Price',
                        'by_fixed' => 'Fixed Amount',
                        'cart_fixed' => 'Fixed Amount to Whole Cart',
                        'buy_x_get_y' => 'Buy X Get Y Free',
                    ])->required(),

                    Select::make('apply_to_shipping')->options([
                        0 => 'No',
                        1 => 'Yes',
                    ])->default(0)->disabled(),

                    Select::make('free_shipping')->options([
                        0 => 'No',
                        1 => 'Yes',
                    ])->required(),

                    Select::make('end_other_rules')->options([
                        0 => 'No',
                        1 => 'Yes',
                    ])->required(),

                ])->columns(2),







            Fieldset::make('Conditions_list')
                ->schema([

                    Select::make('condition_type')
                        ->options([
                            1 => 'Match All Conditions',
                            0 => 'Match Any Condition',
                        ])->required()
                        ->label('Apply By'),

                    Repeater::make('conditions')
                        ->label(__('Condition List'))
                        ->schema([
                        Select::make('attribute')
                            ->label('Choose Condition')
                            ->options($this->conditions->pluck('label', 'key')->toArray())
                            ->columnSpan(function ($state){
                                return empty($state) ? 3 : 1;
                            })
                            ->lazy(),

                        Fieldset::make('options')
                            ->schema(function (callable $get) {
                                if ($get('attribute') !== null) {
                                    // $conditionList = $this->getCondition();
                                    $item = $this->conditions->where('key', $get('attribute'))->first();

                                    if (! empty($item)) {
                                        $field = $this->getConditionField($item);
                                    } else {
                                        $field = [];
                                    }

                                    // return $item['operator'];
                                    return [Select::make('operator')->options($item['operator']), $field];
                                } else {
                                    return [];
                                }
                            })
                            ->label('Details')
                            ->visible(function (\Closure $get){
                                return !empty($get('attribute'));
                            }),

                    ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->collapsible(false),

                ])->columns(1)->label('Condition Details'),













        ];
    }




    public function getConditionField(array $attribute = [])
    {
        if (! empty($attribute)) {
            return match ($attribute['type']) {
                'select' => Select::make('value')
                    ->label('Value')
                    ->options(function () use ($attribute) {
                        return $attribute['options'];
                    })->required(),
                'multiselect' => Select::make('value')->label('Value')
                    ->multiple()
                    ->options(function () use ($attribute) {
                        return $attribute['options'];
                    })->required(),
                default => TextInput::make('value')
                    ->type(function () use ($attribute) {
                        return $attribute['options'] ?? 'text';
                    })->placeholder(function () use ($attribute) {
                        return 'Enter '.$attribute['label'];
                    })->required(),
            };
        } else {
            return [];
        }
    }




}
