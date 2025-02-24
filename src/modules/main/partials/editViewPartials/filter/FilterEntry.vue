<template>
	<div class="filter-entry">
		<div class="selection-fields">
			<NcSelect
				v-model="selectedColumn"
				class="select-field"
				:options="columns"
				label="title"
				:placeholder="t('tables', 'Column')" />
			<NcSelect
				v-if="selectedColumn"
				v-model="selectedOperator"
				class="select-field"
				:options="operators"
				:placeholder="t('tables', 'Operator')" />
			<NcSelect
				v-if="selectedOperator && !selectedOperator.noSearchValue"
				v-model="searchValue"
				class="select-field"
				:options="magicFields"
				:placeholder="getValuePlaceholder"
				@search="v => term = v" />
		</div>
		<NcButton
			:close-after-click="true"
			type="tertiary"
			class="delete-button"
			:aria-label="t('tables', 'Delete filter')"
			@click="$emit('delete-filter')">
			<template #icon>
				<Delete :size="25" />
			</template>
		</NcButton>
	</div>
</template>

<script>
import { NcButton, NcSelect } from '@nextcloud/vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import { ColumnTypes } from '../../../../../shared/components/ncTable/mixins/columnHandler.js'

export default {

	components: {
		NcSelect,
		NcButton,
		Delete,
	},

	props: {
		filterEntry: {
			type: Object,
			default: null,
		},
		columns: {
			type: Array,
			default: null,
		},
	},

	data() {
		return {
			term: '',
		}
	},

	computed: {
		mutableFilterEntry: {
			get() {
				return this.filterEntry
			},
			set(filterEntry) {
				this.$emit('update:filter-entry', filterEntry)
			},
		},
		searchValue: {
			get() {
				// if no value is set, just return ''
				if (this.filterEntry?.value === null || this.filterEntry?.value === '') {
					return ''
				}

				// if the value starts with @, we try to load the magic-value object
				if (this.filterEntry?.value.substr(0, 1) === '@') {
					return this.magicFields.find(item => item.id === this.filterEntry?.value || item.id === this.filterEntry?.value.substr(1))
				}

				return this.filterEntry.value
			},
			set(searchValue) {
				if (typeof searchValue === 'object' && searchValue?.id) {
					this.mutableFilterEntry.value = searchValue.id
				} else if (typeof searchValue === 'string') {
					this.mutableFilterEntry.value = searchValue
				} else {
					this.mutableFilterEntry.value = ''
				}
			},
		},
		selectedColumn: {
			get() {
				return this.columns.find(col => col.id === this.filterEntry.columnId)
			},
			set(column) {
				if (this.operators.length >= 1) {
					this.selectedOperator = this.operators[0]
				} else {
					this.selectedOperator = null
				}

				this.searchValue = null
				this.mutableFilterEntry.columnId = column?.id ?? null
			},
		},
		selectedOperator: {
			get() {
				return this.operators.find(item => this.filterEntry.operator === item.id)
			},
			set(operator) {
				this.mutableFilterEntry.operator = operator?.id
			},
		},
		operators() {
			if (this.selectedColumn) {
				return this.selectedColumn.getPossibleOperators()
			} else {
				return []
			}
		},
		magicFields() {
			if (this.selectedColumn && (this.selectedColumn.type.substr(0, 9) !== 'selection' || this.selectedColumn?.type === 'selection-check')) {
				const fields = []
				this.selectedColumn.getPossibleMagicFields().forEach(field => {
					if (field.id.substr(0, 1) !== '@') {
						const id = field.id
						field.id = '@' + id
					}
					fields.push({ ...field })
				})
				if (this.term) {
					return [this.term, fields]
				} else {
					return fields
				}
			} else if (this.selectedColumn && this.selectedColumn.type.substr(0, 9) === 'selection') {
				const options = []
				this.selectedColumn.selectionOptions?.forEach(item => {
					options.push({
						id: '@selection-id-' + item.id,
						label: item.label,
					})
				})
				return options
			} else {
				return []
			}
		},
		getValuePlaceholder() {
			if (this.selectedColumn.type === ColumnTypes.Datetime) {
				return t('tables', 'JJJJ-MM-DD hh:mm')
			} else if (this.selectedColumn.type === ColumnTypes.DatetimeDate) {
				return t('tables', 'JJJJ-MM-DD')
			} else if (this.selectedColumn.type === ColumnTypes.DatetimeTime) {
				return t('tables', 'hh:mm')
			}
			return t('tables', 'Search Value')
		},
	},
}
</script>

<style>
.filter-entry {
	display: flex;
	justify-content: space-between;
}

.select-field {
	width: 30%;
	padding: calc(var(--default-grid-baseline) * 2);
	min-width: auto !important;
}

.selection-fields {
	flex: 1;
	display: flex;
}

.delete-button {
	margin-left: auto;
}

</style>
