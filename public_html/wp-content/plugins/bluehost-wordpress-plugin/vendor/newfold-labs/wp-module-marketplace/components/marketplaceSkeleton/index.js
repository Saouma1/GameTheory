import './stylesheet.scss';

/**
 * MarketplaceSkeleton Component
 * Use to generate content skeleton
 * 
 * @param {*} props 
 * @returns 
 */
const MarketplaceSkeleton = ({ width, height, customClass }) => {
    return ( 
        <div 
            className={ "newfold-marketplace-skeleton " + ( customClass ) }
            style={{
                "width": width || "100%",
                "height": height || "auto"
            }}>
        </div>
     );
}
 
export default MarketplaceSkeleton;