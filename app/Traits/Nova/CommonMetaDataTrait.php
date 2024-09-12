<?php

namespace App\Traits\Nova;

use App\Models\User;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Trix;

trait CommonMetaDataTrait
{
    /**
     * @return array
     */
    protected function commonMetaData(): array
    {
        return [
            DateTime::make('Created', 'created_at')
                ->displayUsing(fn ($value) => $value ? $value->format('D d/m/Y, g:ia') : '')
                ->sizeOnDetail('w-1/2')
                ->stackedOnDetail(false)
                ->onlyOnDetail(),

            BelongsTo::make('By', 'creator', __CLASS__)
                ->sizeOnDetail('w-1/2')
                ->stackedOnDetail(false)
                ->onlyOnDetail(),

            DateTime::make('Updated', 'updated_at')
                ->displayUsing(fn ($value) => $value ? $value->format('D d/m/Y, g:ia') : '')
                ->sizeOnDetail('w-1/2')
                ->stackedOnDetail(false)
                ->sortable()
                ->exceptOnForms(),

            BelongsTo::make('By', 'editor', __CLASS__)
                ->sizeOnDetail('w-1/2')
                ->stackedOnDetail(false)
                ->sortable()
                ->exceptOnForms(),

            DateTime::make('Deleted', 'deleted_at')
                ->displayUsing(fn ($value) => $value ? $value->format('D d/m/Y, g:ia') : '')
                ->sizeOnDetail('w-1/2')
                ->stackedOnDetail(false)
                ->onlyOnDetail(),

            BelongsTo::make('By', 'destroyer', __CLASS__)
                ->sizeOnDetail('w-1/2')
                ->stackedOnDetail(false)
                ->onlyOnDetail(),

            Trix::make('Changes', function() {
                $history = $this->revisionHistory()->getResults()->reverse();
                $display = "";
                $systemUser = User::find(1);

                foreach ($history as $revision) {
                    $user = $revision->userResponsible();
                    if (!$user) {
                        $user = $systemUser;
                    }
                    $name_pattern = " - <span style='color:green; font-weight:bold'>" . $user->name . "</span> - ";
                    if($revision->key === 'created_at' && !$revision->old_value) {
                        $display .= $revision->created_at . $name_pattern . "<span style='color:blue'>Creation</span></br>";
                    }
                    else if($revision->key === 'deleted_at' && !$revision->old_value) {
                        $display .= $revision->created_at . $name_pattern . "<span style='color:red'>Deletion</span></br>";
                    }
                    else if($revision->key === 'deleted_at' && $revision->old_value) {
                        $display .= $revision->created_at . $name_pattern . "<span style='color:blue'>Restoration</span></br>";
                    }
                    else {
                        $display .= $revision->created_at . $name_pattern . "Field <b>" . $revision->fieldName() . "</b> changed from \"<span style='color:red'>" . $revision->oldValue() . "</span>\" to \"<span style='color:blue'>" . $revision->newValue() ."</span>\"</br>";
                    }
                }
                return $display;

            })->onlyOnDetail(),
        ];
    }
}
