/**
 * THUNDERFIRE Node Status Block
 */

const { registerBlockType } = wp.blocks;
const { TextControl, ToggleControl, SelectControl, PanelBody } = wp.components;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { __ } = wp.i18n;
const { ServerSideRender } = wp.serverSideRender || wp.editor;

registerBlockType('thunderfire/node-status', {
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
					{ title: __('Node Settings', 'thunderfire') },
					wp.element.createElement(TextControl, {
						label: __('Node ID', 'thunderfire'),
						value: attributes.nodeId,
						onChange: function(value) {
							setAttributes({ nodeId: value });
						}
					}),
					wp.element.createElement(ToggleControl, {
						label: __('Show THETA Status', 'thunderfire'),
						checked: attributes.showTheta,
						onChange: function(value) {
							setAttributes({ showTheta: value });
						}
					}),
					wp.element.createElement(SelectControl, {
						label: __('Refresh Interval', 'thunderfire'),
						value: attributes.refreshInterval,
						options: [
							{ label: __('10 seconds', 'thunderfire'), value: 10 },
							{ label: __('30 seconds', 'thunderfire'), value: 30 },
							{ label: __('60 seconds', 'thunderfire'), value: 60 },
							{ label: __('No auto-refresh', 'thunderfire'), value: 0 }
						],
						onChange: function(value) {
							setAttributes({ refreshInterval: parseInt(value, 10) });
						}
					})
				)
			),
			attributes.nodeId
				? wp.element.createElement(ServerSideRender, {
					block: 'thunderfire/node-status',
					attributes: attributes
				})
				: wp.element.createElement(
					'div',
					{ className: 'tf-block-placeholder' },
					__('Enter a Node ID in the block settings.', 'thunderfire')
				)
		);
	},
	save: function() {
		return null; // Server-side render
	}
});
