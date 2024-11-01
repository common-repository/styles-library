/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	MenuGroup,
	MenuItem,
	SearchControl,
} from '@wordpress/components';

/**
 * Renders the block pattern category sidebar control.
 *
 * @since 0.1.0
 * @param {Object} props All the props passed to this function
 * @return {string}      Return the rendered JSX
 */
export default function PatternExplorerSidebar( props ) {
	const {
		patternCategories,
		selectedCategory,
		setSelectedCategory,
		searchValue,
		setSearchValue,
	} = props;

	function onClickCategory( category ) {
		setSelectedCategory( category );
		setSearchValue( '' );
	}

	const baseClassName = 'block-pattern-explorer__sidebar';

	return (
		<div className={ baseClassName }>
			<div className={ `${ baseClassName }__search` }>
				<SearchControl
					value={ searchValue }
					onChange={ setSearchValue }
					label={ __( 'Search patterns', 'ea-styles-library' ) }
				/>
			</div>

			<div className={ `${ baseClassName }__category-type__categories` } >
				<MenuGroup className={ `${ baseClassName }__categories-list` } >
					{ patternCategories.map( ( category ) => {
						return (
							<MenuItem
								key={ category.name }
								label={ category.label }
								className={ `${ baseClassName }__categories-list__item` }
								isPressed={
									! searchValue &&
									category.name ===
										selectedCategory
								}
								onClick={ () =>
									onClickCategory( category.name )
								}
								>
								{ category.label }
							</MenuItem>
						);
					} ) }
				</MenuGroup>
			</div>
		</div>
	);
}
