/**
 * THUNDERFIRE Alert Feed Block
 */

const { registerBlockType } = wp.blocks;
const { RangeControl, PanelBody } = wp.components;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { __ } = wp.i18n;
const { ServerSideRender } = wp.serverSideRender || wp.editor;

registerBlockType('thunderfire/alert-feed', {
	edit: function(props) {
		const { attributes, setAttributes } = props;
		const blockProps = useBlockProps();

		return wp.element.createElement(
			'div',
			blockProps,
			wp.element.createElement(
				InspectorControls,
				null,
				wp.element.createElement(
					PanelBody,
					{ title: __('Alert Settings', 'thunderfire') },
					wp.element.createElement(RangeControl, {
						label: __('Number of Alerts', 'thunderfire'),
						value: attributes.limit,
						onChange: function(value) {
							setAttributes({ limit: value });
						},
						min: 1,
						max: 20
					})
				)
			),
			wp.element.createElement(ServerSideRender, {
				block: 'thunderfire/alert-feed',
				attributes: attributes
			})
		);
	},
	save: function() {
		return null;
	}
});
