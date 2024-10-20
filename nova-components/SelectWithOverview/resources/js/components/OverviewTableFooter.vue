<template>
  <tfoot class="bg-gray-50 dark:bg-gray-800">
    <tr>
      <!-- Select Checkbox -->
      <th
          class="group"
          :class="{
            'divide-x divide-gray-100 dark:divide-gray-700': shouldShowColumnBorders,
          }"
          v-if="shouldShowCheckboxes"
      >
        <span class="sr-only">{{ __('Selected Items') }}</span>
      </th>

      <!-- Field Names -->
      <th
        v-for="(field, index) in fields"
        :key="field"
        :class="{
          [`text-${field.textAlign}`]: true,
          'border-r border-gray-200 dark:border-gray-600':
            shouldShowColumnBorders,
          'px-6': index == 0 && !shouldShowCheckboxes,
          'px-2': index != 0 || shouldShowCheckboxes,
          'whitespace-nowrap': !field.wrapping,
        }"
        class="uppercase text-gray-500 text-xxs py-2 text-left"
      >
        <span>{{ field }}</span>
      </th>

      <!-- View, Edit, and Delete -->
      <th class="uppercase text-xxs tracking-wide px-2 py-2">

      </th>
    </tr>
  </tfoot>
</template>

<script>
export default {
  name: 'OverviewTableFooter',

  emits: ['order', 'reset-order-by'],

  props: {
    shouldShowColumnBorders: Boolean,
    shouldShowCheckboxes: Boolean,
    fields: {
      type: [Object, Array],
    },
  },
  methods: {
  },
}
</script>
