namespace ProjectMetricsMonitor.Application.Exceptions;

public sealed class HttpStatusCodeException : Exception
{
    public int StatusCode { get; }
    public string Title { get; }
    public string? Detail { get; }

    public HttpStatusCodeException(int statusCode, string title, string? detail = null)
        : base(detail ?? title)
    {
        StatusCode = statusCode;
        Title = title;
        Detail = detail;
    }
}
