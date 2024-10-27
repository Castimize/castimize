<template>
  <thead class="bg-gray-50 dark:bg-gray-800">
    <tr>
      <!-- Select Checkbox -->
      <th
        class="w-[1%] white-space-nowrap uppercase text-xxs text-gray-500 tracking-wide pl-5 pr-2 py-2"
        :class="{
          'border-r border-gray-200 dark:border-gray-600':
            shouldShowColumnBorders,
        }"
        v-if="shouldShowCheckboxes"
      >
        <span class="sr-only">{{ __('Selected Items') }}</span>
      </th>

      <!-- Field Names -->
      <th
        v-for="(field, index) in fields"
        :key="field.id"
        :class="{
          [`text-${field.textAlign}`]: true,
          'border-r border-gray-200 dark:border-gray-600':
            shouldShowColumnBorders,
          'px-6': index == 0 && !shouldShowCheckboxes,
          'px-2': index != 0 || shouldShowCheckboxes,
          'whitespace-nowrap': !field.wrapping,
        }"
        class="uppercase text-gray-500 text-xxs tracking-wide py-2 text-left"
      >
<!--        <SortableIcon-->
<!--          @sort="requestOrderByChange(field)"-->
<!--          @reset="resetOrderBy(field)"-->
<!--          :resource-name="resourceName"-->
<!--          :uri-key="field.sortableUriKey"-->
<!--          v-if="sortable && field.sortable"-->
<!--        >-->
<!--          {{ field.indexName }}-->
<!--        </SortableIcon>-->

        <span>{{ field }}</span>
      </th>

      <!-- View, Edit, and Delete -->
      <th class="uppercase text-xxs tracking-wide px-2 py-2">
        <span class="sr-only">{{ __('Controls') }}</span>
      </th>
    </tr>
  </thead>
</template>

<script>
export default {
  name: 'OverviewTableHeader',

  props: {
    shouldShowColumnBorders: Boolean,
    shouldShowCheckboxes: Boolean,
    fields: {
      type: [Object, Array],
    },
    sortable: Boolean,
  },
}
</script>
