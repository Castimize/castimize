<template>
  <div
      class="overflow-hidden overflow-x-auto relative mt-3"
  >
    <table
      class="w-full divide-y divide-gray-100 dark:divide-gray-700"
      dusk="overview-table"
    >
      <OverviewTableHeader
        :fields="overviewHeaders"
        :should-show-checkboxes="shouldShowCheckboxes"
        :should-show-column-borders="shouldShowColumnBorders"
      />
      <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
        <OverviewTableRow
          v-for="(item, index) in overviewItems"
          :checked="selectedItems.indexOf(item) > -1"
          :key="`${item.id}-items-${index}`"
          :item="item"
          :selected-items="selectedItems"
          :should-show-checkboxes="shouldShowCheckboxes"
          :should-show-column-borders="shouldShowColumnBorders"
          :testId="`${item.id}-items-${index}`"
          :update-selection-status="updateSelectionStatus"
        />
      </tbody>
        <OverviewTableFooter
            :fields="overviewFooter"
            :should-show-checkboxes="shouldShowCheckboxes"
            :should-show-column-borders="shouldShowColumnBorders"
        />
    </table>
  </div>
</template>

<script>

import OverviewTableHeader from "./OverviewTableHeader.vue";
import OverviewTableRow from "./OverviewTableRow.vue";
import OverviewTableFooter from "./OverviewTableFooter.vue";

export default {
    components: {OverviewTableRow, OverviewTableHeader, OverviewTableFooter},

  props: {
    overviewHeaders: {
        type: [Object, Array],
    },
    overviewItems: { default: [] },
    overviewFooter: { default: [] },
    selectedItems: { default: [] },
    selectedItemIds: {},
    shouldShowCheckboxes: { type: Boolean, default: false },
    shouldShowColumnBorders: { type: Boolean, default: false },
    updateSelectionStatus: { type: Function },
    sortable: { type: Boolean, default: false },
  },

  data: () => ({
    selectAllResources: false,
    selectAllMatching: false,
    resourceCount: null,
  }),

  computed: {},
}
</script>
