function isValidDomain(domain) {
    const domainPattern = /^((https?:\/\/)?([a-z0-9-]+\.)+[a-z]{2,})$/i;
    return domainPattern.test(domain);
}