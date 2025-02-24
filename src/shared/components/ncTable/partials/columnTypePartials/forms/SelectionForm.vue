<template>
	<div style="width: 100%">
		<div class="row">
			<div class="col-4 title">
				{{ t('tables', 'Options') }}
			</div>
			<div v-for="opt in mutableColumn.selectionOptions" :key="opt.id" class="col-4 inline">
				<NcCheckboxRadioSwitch :value="'' + opt.id" type="radio" :checked.sync="mutableColumn.selectionDefault" />
				<input :value="opt.label" @input="updateLabel(opt.id, $event)">
				<NcButton type="tertiary" :aria-label="t('tables', 'Delete option')" @click="deleteOption(opt.id)">
					<template #icon>
						<DeleteOutline :size="20" />
					</template>
				</NcButton>
			</div>
			<NcButton :aria-label="t('tables', 'Add option')" @click="addOption">
				{{ t('tables', 'Add option') }}
			</NcButton>
			<p class="span">
				{{ t('tables', 'You can set a default value by clicking on one of the radio buttons next to the label fields.') }}
				<a v-if="mutableColumn.selectionDefault" @click="mutableColumn.selectionDefault = ''">{{ t('tables', 'Click here to unset default selection.') }}</a>
			</p>
		</div>
	</div>
</template>

<script>
import { NcCheckboxRadioSwitch, NcButton } from '@nextcloud/vue'
import DeleteOutline from 'vue-material-design-icons/DeleteOutline.vue'
import { translate as t } from '@nextcloud/l10n'

export default {
	name: 'SelectionForm',
	components: {
		NcCheckboxRadioSwitch,
		NcButton,
		DeleteOutline,
	},
	props: {
		column: {
			type: Object,
			default: null,
		},
		canSave: {
			type: Boolean,
			default: true,
		},
	},
	data() {
		return {
			mutableColumn: this.column,
		}
	},
	watch: {
		column() {
			this.mutableColumn = this.column
		},
	},
	created() {
		if (!this.mutableColumn.selectionOptions || this.mutableColumn.selectionOptions?.length === 0) {
			this.mutableColumn.selectionOptions = this.loadDefaultOptions()
		}
	},
	methods: {
		t,
		loadDefaultOptions() {
			const options = [
				{
					id: 0,
					label: t('tables', 'First option'),
				},
				{
					id: 1,
					label: t('tables', 'Second option'),
				},
			]
			return options
		},
		updateLabel(id, e) {
			const i = this.mutableColumn.selectionOptions.findIndex((obj) => obj.id === id)
			const tmp = this.mutableColumn.selectionOptions
			tmp[i].label = e.target.value
			this.mutableColumn.selectionOptions = tmp
		},
		addOption() {
			const nextId = this.getNextId()
			this.mutableColumn.selectionOptions.push({
				id: nextId,
				label: '',
			})
		},
		getNextId() {
			return Math.max(...this.mutableColumn.selectionOptions.map(item => item.id)) + 1
		},
		deleteOption(id) {
			this.mutableColumn.selectionOptions = this.mutableColumn.selectionOptions.filter(opt => opt.id !== id)

			// if deleted option was marked as default
			if (id === parseInt(this.mutableColumn.selectionDefault)) {
				this.mutableColumn.selectionDefault = ''
			}
		},
	},
}
</script>
<style lang="scss" scoped>

.inline {
	display: inline-flex;
}

input {
	margin-top: 8px;
	margin-left: calc(var(--default-grid-baseline) * 1);
}

.col-4.inline {
	margin-left: calc(var(--default-grid-baseline) * 3);
}

</style>
