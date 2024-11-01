import { __ } from "@wordpress/i18n";
import { registerPlugin } from '@wordpress/plugins';
import { PluginBlockSettingsMenuItem, PluginPostStatusInfo } from '@wordpress/edit-post';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { store as noticesStore } from '@wordpress/notices';
import { useCallback, useState, useEffect } from '@wordpress/element';
import { serialize } from '@wordpress/blocks';
import { compact, map } from 'lodash';
import { ReactComponent as BlockPatternIcon }  from '../../../assets/images/block-patterns.svg';

import {
	Button,
	Modal,
	FlexItem,
	Flex,
	TextControl,
	FormTokenField
} from "@wordpress/components";

const BlockPatternLogo = () => {
	return <BlockPatternIcon className="easl-block-pattern-menu-iten-icon" />;
}

const EaStyleLibraryPluginAtEditor = () => {

	const [ isOpen, setOpen ]              = useState( false );
	const [ isAllBlocks, setAllBlocks ]    = useState( false );
	const [ terms, setTerms ]              = useState( [] );
	const [ getTitle, setTitle ]           = useState('');
	const [ getTags, setTags ]             = useState([]);
	const [ getCategories, setCategories ] = useState( [] );

	const { createSuccessNotice, createErrorNotice } = useDispatch(
		noticesStore
	);

	const { selectedBlock, allBlocks, selectedBlocks, selectedClientIds } = useSelect((select) => {
		const { getSelectedBlock, getBlocks, getBlocksByClientId, getSelectedBlockClientIds } = select( blockEditorStore );

		return {
			selectedBlock : getSelectedBlock(),
			allBlocks: getBlocks(),
			selectedBlocks: map(
				compact( getBlocksByClientId( getSelectedBlockClientIds() ) ),
				( block ) => block
			),
			selectedClientIds: getSelectedBlockClientIds(),
		}
	});

	const openModal = () => setOpen( true );
	const closeModal = () => setOpen( false );

	/**
	* Get cpt tags.
	*/
	useEffect(() => {
		const getTerms = async () => {
			const formData = new window.FormData();
			formData.set( 'action' , 'easl_get_terms');
			formData.set( 'nonce', window.ea_block.ajax_nonce );
			const response = await window.fetch(window.ea_block.ajax_url, {
				method: 'POST',
				body: formData,
			});

			const json = await response.json();

			let terms = [];
			if ( json.success ) {
				terms = json.data;
			}

			setTerms(terms);
		};

		getTerms();
	}, []);

	const saveBlockPattern = useCallback(
		async function ( pattern ) {
			try {

				const formData = new window.FormData();
				formData.set( 'action' , 'easl_save_pattern');
				formData.set( 'nonce', window.ea_block.ajax_nonce );
				formData.set( 'title', pattern.title );
				formData.set( 'contents', isAllBlocks ? serialize( allBlocks ) : serialize( selectedBlocks ) );
				formData.set( 'tags', pattern.tags );
				formData.set( 'categories', pattern.categories );

				const response = await window.fetch(window.ea_block.ajax_url, {
					method: 'POST',
					body: formData,
				});

				const json = await response.json();

				if ( json.success ) {
					createSuccessNotice( __( 'New Pattern created.', 'ea-styles-library' ), {
						type: 'snackbar',
					} );
				} else {
					createErrorNotice( json.data, {
						type: 'snackbar',
					} );
				}

			} catch ( error ) {
				createErrorNotice( error.message, {
					type: 'snackbar',
				} );
			} finally {
				setAllBlocks( false );
			}
		},
		[ selectedBlocks, allBlocks, isAllBlocks ]
	);

	return (
		<>
			<PluginBlockSettingsMenuItem
				icon= { <BlockPatternLogo />  }
				label={ __( 'Add to Block Pattern','ea-styles-library') }
				onClick={ openModal }
			/>

			<PluginPostStatusInfo>
				<Button
					variant="secondary"
					onClick={ () => {
						setAllBlocks( true );
						openModal();
					} }
				>
				{ __( 'Add to Block Pattern','ea-styles-library') }
				</Button>
			</PluginPostStatusInfo>

			{ isOpen &&
				<Modal
					className="ea-style-block-to-pattern"
					focusOnMount={ true }
					isDismissible={ true}
					shouldCloseOnEsc={true}
					shouldCloseOnClickOutside={ false }
					title={ __( 'Add to Block Pattern','ea-styles-library') }
					onRequestClose={ closeModal }
				>
					<form
						method="post"
						onSubmit={ ( event ) => {
							event.preventDefault();

							if ( getTitle === "" ) {

								createErrorNotice(
									__( 'Pattern name is missing !', 'ea-styles-library' ),
									{
										type: 'snackbar',
									}
								);

								return;
							}

							saveBlockPattern({
								'title': getTitle,
								'tags': getTags,
								'categories': getCategories
							});

							setTitle('');
							setTags([]);
							setCategories([]);
							closeModal();
						} }
						>

						<TextControl
							label={ __( 'Name', 'ea-styles-librray' )}
							onChange={ ( title ) => setTitle( title ) }
						/>

						<FormTokenField
							label={ __( 'Categories', 'ea-styles-librray' )}
							value={ getCategories }
							suggestions={ terms?.categories || [] }
							placeholder={ __( 'Search/Add categories', 'ea-styles-librray' )}
							__experimentalShowHowTo={false}
							onChange={ ( tokens ) => setCategories( tokens ) }
						/>

						<FormTokenField
							label={ __( 'Keywords', 'ea-styles-librray' )}
							value={ getTags  }
							placeholder={ __( 'Search/Add keywords', 'ea-styles-librray' )}
							suggestions={ terms?.tags || [] }
							onChange={ ( tokens ) => setTags( tokens ) }
						/>

						<Flex
							className="reusable-blocks-menu-items__convert-modal-actions"
							justify="flex-end"
							>
							<FlexItem>
								<Button
									variant="secondary"
									onClick={ closeModal }
									>
									{ __( 'Cancel' ) }
								</Button>
							</FlexItem>

							<FlexItem>
								<Button variant="primary" type="submit">
									{ __( 'Save' ) }
								</Button>
							</FlexItem>
						</Flex>
					</form>
				</Modal>
			}
		</>
	);
}

registerPlugin( 'ea-styles-library', {
	render: EaStyleLibraryPluginAtEditor,
} );
