export function LevelBadge({ level }: { level: string }) {
    return (
        <span className="bg-primary text-primary-foreground inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold">
            👑 {level}
        </span>
    );
}
