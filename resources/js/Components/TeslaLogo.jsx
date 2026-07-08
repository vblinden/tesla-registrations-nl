import teslaLogo from '../../images/tesla-logo.png';

export default function TeslaLogo({ className = 'h-7 w-auto' }) {
    return (
        <img
            src={teslaLogo}
            alt=""
            aria-hidden="true"
            className={`object-contain brightness-0 invert ${className}`}
        />
    );
}
