<template>
    <InlineTextEditableField :field="field" :resourceName="resourceName" :width="300" />
</template>

<script>
import InlineTextEditableField from './InlineTextEditableField';

export default {
    components: { InlineTextEditableField },
    props: ['resourceName', 'field'],
};
</script>
