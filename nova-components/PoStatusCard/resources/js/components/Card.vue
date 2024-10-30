<template>
    <div
        id="po-status-card"
        class="grid gap-4 mb-4 lg:mb-0 items-center justify-between min-h-8"
        :class="`grid-cols-${this.card.statusesCount}`"
    >
        <Card
            v-for="(status, slug) in this.card.statuses"
            class="px-6 py-3"
            :class="{ 'dark:active-dark active-light': this.activeSlug(slug)}"
        >
            <a class="flex items-center justify-center" :href="`/admin/resources/pos/lens/${slug}`">
                <span data-toggle="tooltip" data-placement="top" :title="status">
                  <svg v-if="slug === 'in-queue'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                  <svg v-if="slug === 'in-production'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(234 179 8)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                  <svg v-if="slug === 'available-for-shipping'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(234 179 8)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                  <svg v-if="slug === 'in-transit-to-dc'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(234 179 8)" stroke-width="2"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg>
                  <svg v-if="slug === 'at-dc'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(34 197 94)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                </span>
                <h3 class="text-l ml-3">{{ status }} ({{ this.totals[slug] }})</h3>
            </a>
        </Card>
    </div>
</template>

<script>

export default {

    props: [
        'card',
    ],

    data: () => ({
        totals: [],
        statuses: [],
        refreshInterval: null,
    }),

    methods: {
        getTotals: async function () {
            const {
                data: {totals},
            } = await Nova.request().post(
                '/nova-vendor/po-status-card/get-totals',
                {
                    statuses: this.statuses
                }
            );
            this.totals = totals;
            setTimeout(this.getTotals, this.refreshInterval);
        },

        activeSlug(slug) {
            return this.card.activeSlug === slug;
        },
    },

  mounted() {
        console.log(this.card.activeSlug);
      this.statuses = this.card.statuses;
      this.refreshInterval = this.card.refreshIntervalSeconds;
      this.getTotals();
      if (this.refreshInterval) {
          this.refreshInterval = this.refreshInterval * 1000;
      }
  },
}
</script>
