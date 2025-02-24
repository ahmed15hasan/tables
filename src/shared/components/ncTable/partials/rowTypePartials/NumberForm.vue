<template>
	<RowFormWrapper :width="2" :title="column.title" :mandatory="column.mandatory" :description="column.description">
		<div v-if="column.numberPrefix" class="prefix">
			{{ column.numberPrefix }}
		</div>
		<input v-model="localValue"
			type="number"
			:min="column.numberMin"
			:max="column.numberMax"
			:step="getStep">
		<div v-if="column.numberSuffix" class="suffix">
			{{ column.numberSuffix }}
		</div>
	</RowFormWrapper>
</template>

<script>
import RowFormWrapper from './RowFormWrapper.vue'

export default {

	components: {
		RowFormWrapper,
	},

	props: {
		column: {
			type: Object,
			default: null,
		},
		value: {
			type: Number,
			default: null,
		},
	},

	computed: {
		getStep() {
			if (this.column?.numberDecimals === 0) {
				return '1'
			} else if (this.column?.numberDecimals > 0) {
				return '.' + '0'.repeat(this.column.numberDecimals - 1) + '1'
			} else {
				return 'any'
			}
		},
		localValue: {
			get() {
				if (this.value !== null) {
					return this.value
				} else {
					if (this.column.numberDefault !== undefined) {
						this.$emit('update:value', this.column.numberDefault)
						return this.column.numberDefault
					} else {
						return null
					}
				}
			},
			set(v) { this.$emit('update:value', this.parseValue(v)) },
		},
	},

	watch: {
		localValue() {
			const value = this.parseValue(this.localValue)
			this.localValue = value
			this.$emit('update:value', value)
		},
		value() {
			this.localValue = this.value
		},
	},

	mounted() {
		this.localValue = this.value
	},

	methods: {
		parseValue(inputValue) {
			const parsedValue = parseFloat(inputValue)
			const roundedValue = parsedValue.toFixed(this.column?.numberDecimals)
			return parseFloat(roundedValue)
		},
	},
}
</script>
<style lang="scss" scoped>

.prefix {
	padding-right: calc(var(--default-grid-baseline) * 2);
}

.suffix {
	padding-left: calc(var(--default-grid-baseline) * 2);
}

</style>
