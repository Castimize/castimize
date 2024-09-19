<?php

namespace App\Traits\Nova;

use App\Models\User;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Trix;

trait CommonMetaDataTrait
{
    /**
     * @param bool $showCreatedAtOnIndex
     * @param bool $showCreatorOnIndex
     * @param bool $showUpdatedAtOnIndex
     * @param bool $showEditorOnIndex
     * @return array
     */
    protected function commonMetaData(bool $showCreatedAtOnIndex = false, bool $showCreatorOnIndex = false, bool $showUpdatedAtOnIndex = true, bool $showEditorOnIndex = true): array
    {
        $createdAt = DateTime::make(__('Created'), 'created_at')
            ->displayUsing(fn ($value) => $value ? $value->format('D d/m/Y, g:ia') : '')
            ->sizeOnDetail('w-1/2')
            ->hideWhenCreating()
            ->hideWhenUpdating()
            ->stackedOnDetail(false)
            ->sortable();
        if (!$showCreatedAtOnIndex) {
            $createdAt->onlyOnDetail();
        }
        $creator = BelongsTo::make(__('By'), 'creator', __CLASS__)
            ->sizeOnDetail('w-1/2')
            ->hideWhenCreating()
            ->hideWhenUpdating()
            ->stackedOnDetail(false)
            ->sortable();
        if (!$showCreatorOnIndex) {
            $creator->onlyOnDetail();
        }
        $updatedAt = DateTime::make(__('Updated'), 'updated_at')
            ->displayUsing(fn ($value) => $value ? $value->format('D d/m/Y, g:ia') : '')
            ->sizeOnDetail('w-1/2')
            ->stackedOnDetail(false)
            ->sortable()
            ->exceptOnForms();
        if (!$showUpdatedAtOnIndex) {
            $updatedAt->onlyOnDetail();
        }
        $editor = BelongsTo::make(__('By'), 'editor', __CLASS__)
            ->sizeOnDetail('w-1/2')
            ->hideWhenCreating()
            ->hideWhenUpdating()
            ->stackedOnDetail(false)
            ->sortable()
            ->exceptOnForms();
        if (!$showEditorOnIndex) {
            $editor->onlyOnDetail();
        }
        return [
            $createdAt,
            $creator,
            $updatedAt,
            $editor,
            DateTime::make(__('Deleted'), 'deleted_at')
                ->displayUsing(fn ($value) => $value ? $value->format('D d/m/Y, g:ia') : '')
                ->sizeOnDetail('w-1/2')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->stackedOnDetail(false)
                ->onlyOnDetail(),

            BelongsTo::make(__('By'), 'destroyer', __CLASS__)
                ->sizeOnDetail('w-1/2')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->stackedOnDetail(false)
                ->onlyOnDetail(),

            Trix::make(__('Changes'), function() {
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
                        $display .= $revision->created_at . $name_pattern . "<span style='color:blue'>" . __('Creation') . "</span></br>";
                    }
                    else if($revision->key === 'deleted_at' && !$revision->old_value) {
                        $display .= $revision->created_at . $name_pattern . "<span style='color:red'>" . __('Deletion') . "</span></br>";
                    }
                    else if($revision->key === 'deleted_at' && $revision->old_value) {
                        $display .= $revision->created_at . $name_pattern . "<span style='color:blue'>" . __('Restoration') . "</span></br>";
                    }
                    else {
                        $display .= $revision->created_at . $name_pattern . __('Field') . " <b>" . $revision->fieldName() . "</b> " . __('changed from') . " \"<span style='color:red'>" . $revision->oldValue() . "</span>\" " . __('to') . " \"<span style='color:blue'>" . $revision->newValue() ."</span>\"</br>";
                    }
                }
                return $display;

            })->onlyOnDetail(),
        ];
    }
}
