/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __, _n, sprintf } from '@wordpress/i18n';
import {
	Button,
	DropdownMenu,
	MenuGroup,
	MenuItem,
	Spinner,
} from '@wordpress/components';
import { check, stretchFullWidth, category } from '@wordpress/icons';
import { ButtonGroup } from '@wordpress/components';

/**
 * Renders the block pattern preview header.
 *
 * @since 0.1.0
 * @param {Object} props All the props passed to this function
 * @return {string}      Return the rendered JSX
 */
export default function PreviewHeader( props ) {
	const {
		apiResponse,
		viewportWidth,
		setViewportWidth,
		isGrid,
		setIsGrid,
		shownPatterns,
		searchValue,
		isLoading,
		patternType,
		setPatternType
	} = props;

	const widths = [
		{
			label: __( 'Desktop', 'ea-styles-library' ),
			slug: 'desktop',
			value: 1300,
			active: viewportWidth === 1300,
		},
		{
			label: __( 'Tablet', 'ea-styles-library' ),
			slug: 'tablet',
			value: 778,
			active: viewportWidth === 778,
		},
		{
			label: __( 'Mobile', 'ea-styles-library' ),
			slug: 'mobile',
			value: 358,
			active: viewportWidth === 358,
		},
	];

	function toggleWidths( width ) {
		if ( ! width.active ) {
			setViewportWidth( width.value );
		}
	}

	const baseClassName = 'block-pattern-explorer__preview-header';

	return (
		<div className={ baseClassName }>

			{ apiResponse &&
				<div className={ `${ baseClassName }__pattern-types` } >
					<ButtonGroup className='easl-pattern-btn-group'>
						{ Object.keys(apiResponse).map( ( key ) => (
							<Button
								key={key}
								label={ apiResponse[key]?.name }
								variant="secondary"
								isPressed={ patternType === key }
								onClick={ () => setPatternType( key ) }
							>
							{ apiResponse[key]?.name }
							</Button>
						) ) }
					</ButtonGroup>
				</div>
			}

			<div className={ `${ baseClassName }__search-results` }>
				{ isLoading && <Spinner /> }
				{ searchValue &&
					searchValue.length > 1 &&
					sprintf(
						// translators: %1$d: Number of patterns. %2$s: The search input.
						_n(
							'%1$d search result for "%2$s"',
							'%1$d search results for "%2$s"',
							shownPatterns.length,
							'ea-styles-library'
						),
						shownPatterns.length,
						searchValue
					) }
			</div>

			<div className={ `${ baseClassName }__controls` }>
				<DropdownMenu
					icon={ '' }
					text={ __( 'Preview', 'ea-styles-library' ) }
					className="viewport-toggle"
					toggleProps={ { isTertiary: true } }
					popoverProps={ {
						focusOnMount: 'container',
						position: 'bottom left',
					} }
				>
					{ () => (
						<MenuGroup>
							{ widths.map( ( width ) => (
								<MenuItem
									key={ width.slug }
									className={ classnames( {
										disabled: ! width.active,
									} ) }
									icon={ width.active ? check : '' }
									onClick={ () => toggleWidths( width ) }
								>
									{ width.label }
								</MenuItem>
							) ) }
						</MenuGroup>
					) }
				</DropdownMenu>
				<Button
					label={ __(
						'Individual Pattern',
						'ea-styles-library'
					) }
					icon={ stretchFullWidth }
					isPressed={ ! isGrid }
					onClick={ () => setIsGrid( ! isGrid ) }
				/>
				<Button
					label={ __( 'Grid View', 'ea-styles-library' ) }
					icon={ category }
					isPressed={ isGrid }
					onClick={ () => setIsGrid( ! isGrid ) }
				/>
			</div>
		</div>
	);
}
