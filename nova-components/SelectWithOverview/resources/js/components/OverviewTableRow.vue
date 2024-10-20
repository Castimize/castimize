<template>
  <tr
    :dusk="`${item.id}-row`"
    class="group"
    :class="{
      'divide-x divide-gray-100 dark:divide-gray-700': shouldShowColumnBorders,
    }"
    @click.stop.prevent="handleClick"
  >
    <!-- Resource Selection Checkbox -->
    <td
      v-if="shouldShowCheckboxes"
      :class="{
        'py-2': !shouldShowTight,
      }"
      class="w-[1%] white-space-nowrap pl-5 pr-5 dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900"
      @click.stop
    >
      <Checkbox
        v-if="shouldShowCheckboxes"
        @change="toggleSelection"
        :model-value="checked"
        :dusk="`${item.id}-checkbox`"
        :aria-label="__('Select item')"
      />
    </td>

    <!-- Item columns -->
    <td
      v-for="(column, index) in item"
      :key="index"
      :class="{
        'px-6': index === 0 && !shouldShowCheckboxes,
        'px-2': index !== 0 || shouldShowCheckboxes,
        'py-2': !shouldShowTight,
        'cursor-pointer': clickableRow,
      }"
      class="dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900"
    >
        {{ column }}
    </td>
  </tr>
</template>

<script>
import { Button, Checkbox, Icon } from 'laravel-nova-ui'

export default {
  components: {
    Button,
    Checkbox,
    Icon,
  },

  emits: ['actionExecuted'],

  inject: [
    'resourceHasId',
    'authorizedToViewAnyResources',
    'authorizedToUpdateAnyResources',
    'authorizedToDeleteAnyResources',
    'authorizedToRestoreAnyResources',
  ],

  props: [
    'checked',
    'queryString',
    'item',
    'resourceName',
    'itemsSelected',
    'selectedItems',
    'shouldShowCheckboxes',
    'shouldShowColumnBorders',
    'testId',
    'updateSelectionStatus',
  ],

  data: () => ({
    commandPressed: false,
  }),

  beforeMount() {
    this.isSelected = this.selectedItems.indexOf(this.item) > -1
  },

  mounted() {
    window.addEventListener('keydown', this.handleKeydown)
    window.addEventListener('keyup', this.handleKeyup)
  },

  beforeUnmount() {
    window.removeEventListener('keydown', this.handleKeydown)
    window.removeEventListener('keyup', this.handleKeyup)
  },

  methods: {
    /**
     * Select the resource in the parent component
     */
    toggleSelection() {
      this.updateSelectionStatus(this.resource)
    },

    handleKeydown(e) {
      if (e.key === 'Meta' || e.key === 'Control') {
        this.commandPressed = true
      }
    },

    handleKeyup(e) {
      if (e.key === 'Meta' || e.key === 'Control') {
        this.commandPressed = false
      }
    },

    handleClick(e) {
        return this.toggleSelection()
    },
  },

  computed: {

    shouldShowTight() {
      return false;
    },

    clickableRow() {
      return true;
    },
  },
}
</script>
