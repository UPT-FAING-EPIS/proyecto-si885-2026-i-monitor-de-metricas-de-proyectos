namespace ProjectMetricsMonitor.Domain.Entities;

public sealed class User
{
    public Guid Id { get; set; }
    public string SupabaseId { get; set; } = string.Empty;
    public string FullName { get; set; } = string.Empty;
    public string Email { get; set; } = string.Empty;
    public string Provider { get; set; } = string.Empty;
    public DateTimeOffset CreatedAt { get; set; }
    public DateTimeOffset UpdatedAt { get; set; }
}

