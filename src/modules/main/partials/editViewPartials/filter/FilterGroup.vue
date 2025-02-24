<template>
	<div class="filter-group">
		<div class="group-text">
			{{ t('tables', '... that meet all of the following conditions') }}
		</div>
		<div v-for="(filter, index) in mutableFilterGroup"
			:key="index">
			<FilterEntry
				:filter-entry.sync="mutableFilterGroup[index]"
				:columns="columns"
				:class="{'locallyAdded': isLocallyAdded(filter)}"
				@delete-filter="deleteFilter(index)" />
		</div>
		<NcButton
			:close-after-click="true"
			type="tertiary"
			:aria-label="t('tables', 'Add new filter')"
			@click="addFilter">
			{{ t('tables', 'Add new filter') }}
			<template #icon>
				<Plus :size="25" />
			</template>
		</NcButton>
	</div>
</template>

<script>
import FilterEntry from './FilterEntry.vue'
import { NcButton } from '@nextcloud/vue'
import Plus from 'vue-material-design-icons/Plus.vue'

export default {
	name: 'FilterGroup',
	components: {
		FilterEntry,
		NcButton,
		Plus,
	},
	props: {
		filterGroup: {
			type: Array,
			default: null,
		},
		viewFilterGroup: {
			type: Array,
			default: null,
		},
		generatedFilterGroup: {
			type: Array,
			default: null,
		},
		columns: {
			type: Array,
			default: null,
		},
	},
	computed: {
		mutableFilterGroup: {
			get() {
				return this.filterGroup
			},
			set(filterGroup) {
				this.$emit('update:filter-group', filterGroup)
			},
		},
	},
	methods: {
		isSameEntry(object, searchObject) {
			return Object.keys(searchObject).every((key) => object[key] === searchObject[key])
		},
		isLocallyAdded(filter) {
			if (!this.viewFilterGroup || !this.generatedFilterGroup) return false
			return this.generatedFilterGroup.some(e => this.isSameEntry(e, filter)) && !this.viewFilterGroup.some(e => this.isSameEntry(e, filter))
		},
		addFilter() {
			this.mutableFilterGroup.push({ columnId: null, operator: null, value: '' })
		},
		deleteFilter(index) {
			this.mutableFilterGroup.splice(index, 1)
			if (this.mutableFilterGroup.length === 0) {
				this.$emit('delete-filter-group')
			}
		},
	},
}
</script>

<style>

.group-text {
	padding-left: calc(var(--default-grid-baseline) * 2);
}

.filter-group {
	border-left: 6px solid var(--color-primary) !important;
	padding-left: calc(var(--default-grid-baseline) * 2);
}

.locallyAdded {
	background-color: var(--color-success-hover);
}
</style>
