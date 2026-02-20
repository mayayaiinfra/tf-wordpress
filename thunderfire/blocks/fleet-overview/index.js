/**
 * THUNDERFIRE Fleet Overview Block
 */

const { registerBlockType } = wp.blocks;
const { useBlockProps } = wp.blockEditor;
const { __ } = wp.i18n;
const { ServerSideRender } = wp.serverSideRender || wp.editor;

registerBlockType('thunderfire/fleet-overview', {
	edit: function(props) {
		const blockProps = useBlockProps();

		return wp.element.createElement(
			'div',
			blockProps,
			wp.element.createElement(ServerSideRender, {
				block: 'thunderfire/fleet-overview',
				attributes: props.attributes
			})
		);
	},
	save: function() {
		return null;
	}
});
